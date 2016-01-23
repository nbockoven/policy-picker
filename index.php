<?
  require_once( 'Bootstrap.php' );

  $connection = new DB_connection();

  if( !empty($_POST) ){
    // collect action to determine what to call
    $action = $_POST['action'];
    // collect dataType to return
    $dataType = $_POST['dataType'];
    // no longer needed, and don't want it added to queries
    unset( $_POST['action'] );
    unset( $_POST['dataType'] );

    switch( $action ){
      case 'add':
      case 'insert':
      case 'save':
      case 'update':
        $confirmation = $connection->save( $_POST );
        break;
      case 'delete':
        $confirmation = $connection->delete( $_POST );
        break;
      default: // get
        $confirmation = $connection->get( $_POST );
    }
    // return JSON results if specified
    if( $dataType && strtolower( $dataType ) === 'json' )
      $confirmation = json_encode( $confirmation );
    die( $confirmation ); // return for ajax calls
  }
  else{
    // get list of countries for form
    $params       = ['table' => 'countries', 'orderBy' => 'title'];
    $confirmation = $connection->get( $params );
    $countries = [];
    foreach( $confirmation['results'] as $country )
      $countries[] = ['value' => $country->code, 'label' => mb_convert_encoding(addslashes( $country->title ), 'UTF-8')];
    $countries = json_encode( $countries );
    // get list of states for form
    $params       = ['table' => 'states', 'orderBy' => 'title'];
    $confirmation = $connection->get( $params );
    $states = [];
    foreach( $confirmation['results'] as $state )
      $states[] = ['value' => $state->code, 'label' => mb_convert_encoding(addslashes( $state->title ), 'UTF-8')];
    $states = json_encode( $states );
  }
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Policy Picker</title>

    <!-- Materialize -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- custom -->
    <link rel="stylesheet" href="css/core-layout.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="grey lighten-2">

    <?
      include 'components/navbar.html';
      include 'components/parallax.html';
    ?>



    <div class="row">
      <div class="col s12 m4">
        <? include 'components/form.html' ?>
      </div><!-- .col -->

      <div class="col s12 m8">
        <? include 'components/policies.html' ?>
      </div><!-- .col -->
    </div><!-- .row -->

    <div class="row">
      <div class="col s12">
        <? include 'components/records.html' ?>
      </div><!-- .col -->
    </div><!-- .row -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
    <script src="js/magic.js"></script>
    <script>
      $(document).ready(function(){
        $('[list="list-country"]').autocomplete({"source": <? echo $countries ?>, close: function(){$(this).change()}});
        $('[list="list-state"]').autocomplete({"source": <? echo $states ?>, close: function(){$(this).change()}});
      });
    </script>
  </body>
</html>
