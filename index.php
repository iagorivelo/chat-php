<?php

    include './logic/chatControl.php';

    if(isset($_SESSION['username']) && !empty($_SESSION['username']))
    {
        $message = "
        <span id='6269d77081ed0d003f6f4fd002dae3a8' class='disconnect-message'>
            <b>".$_SESSION['username']." Desconectou-se</b>
        </span><br>";
    
        file_put_contents('./logic/static/messages.txt', $message . PHP_EOL, FILE_APPEND);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 Home</title>
    <link rel="stylesheet" href="public/styles/home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</head>
<body>

    <h1>💬 Bate papo</h1>

    <section>
	    <form method="POST" action="./chat.php">
	    	<input name="user" required id="user" style="margin-bottom: 10px;" type='text' placeholder='Digite seu belo nome' class='form-control' />
	        <input class="btn btn-success" type="submit" value="Bater um papo" />
        </form>
    </section>

</body>
</html>
