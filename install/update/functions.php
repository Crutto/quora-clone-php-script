<?php
function escape_value( $value ) {
	if( function_exists( "mysqli_real_escape_string" )) { // PHP v4.3.0 or higher
		if(get_magic_quotes_gpc()) { $value = stripslashes( $value ); }
		$value = mysqli_real_escape_string( $value );
	} else { // before PHP v4.3.0
		if( !get_magic_quotes_gpc()) { $value = addslashes( $value ); }
	}
	return $value;
}

function redirect_to( $location = NULL ) {
  if ($location != NULL) {
    header("Location: {$location}");
    exit;
  }
}
	
function SplitSQL($file, $delimiter = ';',$con) {
    set_time_limit(0);
    if (is_file($file) === true) {
        $file = fopen($file, 'r');
        if (is_resource($file) === true) {
            $query = array();
            while (feof($file) === false) {
                $query[] = fgets($file);
                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                    $query = trim(implode('', $query));
                    if (!mysqli_query($con,$query)) {
                        return 'ERROR: ' . mysqli_error($con);
                    }
                    flush();
                }
                if (is_string($query) === true) {
                    $query = array();
                }
            }
            fclose($file);
        }
    }
    return 'finished';
}

?>