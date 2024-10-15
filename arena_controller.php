<?php

require('./configs/config.php');
require_once('./api/PwAPI.php');

$api = new API();

$argv[1]($argv[2]);

function arenaMonitor($line = null) {
    global $config;
    global $api;

    $mysqli = new mysqli($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['password'], $config['mysql']['db']);
    
    if (strpos($line, "msg=YQByAGUAbgBhADEAdgAxAA==") !== false and strpos($line, "chl=0") !== false) {
        preg_match('/src=(\d+)/', $line, $matches);
        $roleId = $matches[1]; 
        $roleBase = $api->getRoleBase($roleId);
        $roleName = $roleBase["name"]; 
        
        $checkPlayer = $mysqli->query("SELECT * FROM arena_players WHERE role_id = '$roleId'");
        $checkBattle = $mysqli->query("SELECT * FROM arena_game WHERE (player1 = '$roleId' OR player2 = '$roleId') AND status = 'ongoing'");
        

        $checkTime = $mysqli->query("SELECT start_time FROM arena_game WHERE (player1 = '$roleId' OR player2 = '$roleId') ORDER BY start_time DESC LIMIT 1");
        if ($checkTime->num_rows > 0) {
            $lastBattle = $checkTime->fetch_assoc();
            $startTime = strtotime($lastBattle['start_time']);
            $currentTime = time();
            $timeDiff = ($currentTime - $startTime) / 60; 
            if ($timeDiff < 60) {
                $remainingTime = 60 - $timeDiff;
                $api->chatWhisper($roleId, $roleName, "Você só pode participar da arena novamente em " . round($remainingTime) . " minutos.");
                exit;
            } else {
                // Remove o jogador da arena_game após 1 hora
                $mysqli->query("DELETE FROM arena_game WHERE (player1 = '$roleId' OR player2 = '$roleId') AND status = 'finished'");
            }
        }

        if ($checkPlayer->num_rows > 0 || $checkBattle->num_rows > 0) {
            $api->chatWhisper($roleId, $roleName, "Você já está em um pareamento ou batalha. Aguarde o término antes de tentar novamente.");
            exit;
        }


        $result = $mysqli->query("SELECT * FROM arena_players WHERE status = 'waiting' LIMIT 1");
        
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $opponentId = $row['role_id'];
            $opponentName = $api->getRoleBase($opponentId)['name'];


            $api->sendMail($roleId, "Pareamento da Arena", "Você foi pareado para a Arena 1VS1, boa sorte!", $config['itemA'], $config['money']);
            $api->sendMail($opponentId, "Pareamento da Arena", "Você foi pareado para a Arena 1VS1, boa sorte!", $config['itemB'], $config['money']);


            $mysqli->query("DELETE FROM arena_players WHERE role_id IN ($roleId, $opponentId)");


            $mysqli->query("INSERT INTO arena_game (player1, player2, status, start_time) VALUES ('$roleId', '$opponentId', 'ongoing', NOW())");


            $api->chatInGame("[ARENA] $roleName e $opponentName estão pareados para a Arena 1VS1!");
        } else {

            $mysqli->query("INSERT INTO arena_players (role_id, status) VALUES ('$roleId', 'waiting')");
            $api->chatInGame("[ARENA] $roleName iniciou o pareamento da Arena no modo 1vs1, aguardando um oponente...");
        }
    } else {
        exit;
    }
}
?>
