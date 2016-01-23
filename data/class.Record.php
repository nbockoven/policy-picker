<?
/**
* Author: Nathaniel Bockoven
* Created: 2016-01-22
*/
class Record {
  public $id;
  public $citizenship;
  public $primary_destination;
  public $state_code;
  public $birth_date;
  public $start_travel_date;
  public $end_travel_date;
  public $status;
  public $dependent;

  public function __construct( $data = null ){
    if( is_array( $data ) ) {
      foreach( $data as $key => $value ){
        if( isset( $this->$key ) )
          $this->$key = $value;
      }
    }
  }
}
