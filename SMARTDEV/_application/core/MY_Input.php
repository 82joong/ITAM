<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
  REMOTE_ADDR 오류 수정    

 **/
class MY_Input extends CI_Input{
	/**
	 * Fetch the IP Address
	 *
	 * @return	string
	 */
	public function ip_address()
	{
		// CLI 모드 실행시 warnning 없애기.
		if(!isset($_SERVER['REMOTE_ADDR'])){
			$this->ip_address = '0.0.0.0';
			return $this->ip_address;
		}
		return parent::ip_address();
	}

	/**
	 * Fetch an item from the FILES array
	 *
	 * @param	mixed	$index		Index for item to be fetched from $_FILES
	 * @param	bool	$xss_clean	Whether to apply XSS filtering
	 * @return	mixed
	 */
	public function files($index = null, $xss_clean = null)
	{
		return $this->_fetch_from_array($_FILES, $index, $xss_clean);
	}
}
?>
