<?

/**
* Author: Nathaniel Bockoven
* Created: 2016-01-22
* Purpose: Connect to database
*/
class DB_connection {

  private $connection;

  function __construct(){
    $this->connection = new PDO('mysql:host=localhost;dbname=nbockove_policy_picker', 'nbockove_me', 'x^ANgTP=#zEy');
    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }


  /**
  * Purpose: Bind param values to PDO variables
  */
  private function bindValues( $query, $params ){
    if( $params ){
      foreach( $params as $field => $value )
        $query->bindValue(':'.$field, $value);
    }
    return $query;
  }


  /**
  * Purpose: Build query's WHERE clause
  */
  private function buildWhereClause( $params ){
    // if params have been received,
    // then build query's WHERE clause
    $where = [];
    if( $params ){
      foreach( array_keys( $params ) as $field )
        $where[] = $field . ' = :' . $field;
    }
    // put it all together
    return ( empty( $where ) ) ? '' : ' WHERE ' . implode(' AND ', $where);
  }


  /**
  * Purpose: Delete record(s)
  */
  public function delete( $params ){
    $confirmation = [];
    if( intval( $params['id'] ) ){
      try{
        // build query's WHERE clause
        $where = self::buildWhereClause( $params );
        // prepare query object
        $sql = 'DELETE FROM records ' . $where;

        $query = $this->connection->prepare( $sql );
        // bind values to query object
        $query = self::bindValues( $query, $params );
        // execute query
        $query->execute();

        // set confirmation
        $confirmation = [
          'status'     => 'success',
          'text-color' => 'green',
          'message'    => 'Removal successful!'
        ];
      }
      catch( PDOException $e ){
        // set confirmation
        $confirmation = [
          'status'     => 'error',
          'text-color' => 'red',
          'message'    => 'Removal failed.',
          'console'    => $e->getMessage()
        ];
      }
    }
    else{
      $confirmation = [
        'status'     => 'warning',
        'text-color' => 'orange',
        'message'    => 'Nothing to remove.'
      ];
    }
    // return confirmation
    if( $params['dataType'] && strtolower( $params['dataType'] ) === 'json' )
      $confirmation = json_encode( $confirmation );
    return $confirmation;
  }


  /**
  * Purpose: Get record(s)
  */
  public function get( $params ){
    $confirmation = [];
    if( $params['table'] ){
      try{
        // build query's WHERE clause
        $where = self::buildWhereClause( $params['where'] );
        // build query's ORDER BY clause
        $order = ( $params['orderBy'] ) ? ' ORDER BY ' . $params['orderBy'] : '';
        // prepare query object
        if( $params['table'] === 'records' ){
          $sql = "SELECT
                    r.*,
                    c.title citizenship_title,
                    d.title primary_destination_title,
                    s.title state_title
                  FROM records r
                  JOIN countries c ON r.citizenship = c.code
                  JOIN countries d ON r.primary_destination = d.code
                  LEFT JOIN states s ON r.state_code = s.code " . $where . $order;
        }
        else{
          $sql = 'SELECT * FROM '. $params['table'] . ' ' . $where . $order;
        }

        // die( $sql );
        $query = $this->connection->prepare( $sql );
        // bind values to query object
        $query = self::bindValues( $query, $params['where'] );

        // execute query
        $query->execute();

        switch( $params['table'] ){
          case 'countries':
            $query->setFetchMode(PDO::FETCH_CLASS, 'Country');
            break;
          case 'states':
            $query->setFetchMode(PDO::FETCH_CLASS, 'State');
            break;
          case 'records':
            $query->setFetchMode(PDO::FETCH_CLASS, 'Record');
        }

        // set confirmation
        $confirmation = [
          'status'     => 'success',
          'text-color' => 'green',
          'message'    => 'Retrieved '. $query->rowCount() .' results.',
          'results'    => $query->fetchAll()
        ];
      }
      catch( PDOException $e ){
        // set confirmation
        $confirmation = [
          'status'     => 'error',
          'text-color' => 'red',
          'message'    => 'Retrieval failed.',
          'console'    => $e->getMessage()
        ];
      }
    }
    // return confirmation
    return $confirmation;
  }


  /**
  * Purpose: INSERT or UPDATE record
  */
  public function save( $params ){
    // initial confirmation
    $confirmation = [
      'status'     => 'warning',
      'text-color' => 'orange',
      'message'    => 'Nothing to save.'
    ];

    // check for submitted data
    if( $params ){
      $params['dependent'] = ( $params['dependent'] ) ? $params['dependent'] : 0;
      $fields = array_keys( $params );
      $sql = "INSERT INTO records (".implode(', ', $fields).")
               VALUES (:".implode(', :', $fields).")
               ON DUPLICATE KEY UPDATE ";
      $comma = '';
      foreach( $fields as $field ){
        $sql .= $comma.$field . ' = :' . $field;
        $comma = ', ';
      }

      try{
        // prepare query object
        $query = $this->connection->prepare( $sql );
        // bind values to query object
        $query = self::bindValues( $query, $params );
        // execute query
        $query->execute();

        // set confirmation
        $confirmation = [
          'status'     => 'success',
          'text-color' => 'green',
          'message'    => 'Save successful!',
        ];
      }
      catch( PDOException $e ){
        // set confirmation
        $confirmation = [
          'status'     => 'error',
          'text-color' => 'red',
          'message'    => 'Error occurred while attempting to save.',
          'console'    => $e->getMessage()."\nSQL: ".$sql
        ];
      }
    }
    return $confirmation;
  }

}
