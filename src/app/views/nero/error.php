<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>Nero error</title>

	<!-- Import fonts and bootstrap -->
        <link href='https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>

	<!-- Custom style -->
        <style>
         * {
             margin: 0;
             padding: 0;
             font-family: 'Open Sans Condensed', sans-serif;
             font-size: 1.1em;
         }
         
         h1 {
             text-align: center;
             margin-top: 50px;
             color: black;
             font-size: 4em;
         }

	 h3.exception {
	     padding: 20px;
	     background: #eee;
	     border-radius: 10px;
	     border: 2px solid gray;
	     text-align: center;
	 }
        </style>
    </head>
    <body>
        <div class="container">
	    <!-- Heading -->
	    <div class="row">
		<h1>Nero exception</h1>
		<h3 class="exception"><?= $exception_name; ?> : <?= $exception->getMessage() ?></h3>
	    </div>
	    
	    <!-- Extract the needed info -->
            <?php
            $traceString = $exception->getTraceAsString();
            $explodedTrace = explode('#', $traceString);
            unset($explodedTrace[0]);
            ?>

	    <!-- Stack trace -->
	    <div class="row">
		<ul class="list-group">
                    <p>Stack trace: </p>
                <?php foreach($explodedTrace as $line): ?>
                    <li class="list-group-item"><?= $line; ?></li>
                <?php endforeach; ?>
		</ul>
	    </div>
        </div>
    </body>
</html>
