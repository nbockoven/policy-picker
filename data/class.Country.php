<?
/**
* Author: Nathaniel Bockoven
* Created: 2016-01-22
*/
class Country {
  public $code;
  public $title;

  public function __construct( $data = null ){
    if( is_array( $data ) ) {
      foreach( $data as $key => $value ){
        if( isset( $this->$key ) )
          $this->$key = $value;
      }
    }
  }
}
