<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once dirname(__FILE__).'/Base_shop.php';

class Main extends Base_shop {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->common->locationhref('/admin');
		return;
		$this->load->model('config_tb_model');

		echo 'config row count is <br />';
		echo $this->config_tb_model->getCount()->getData();
		echo '<br />';
		echo 'last query is ';
		echo '<br />';
		echo $this->config_tb_model->getLastQuery();
		echo '<br />';
		echo '<br />';

		echo 'config row Array is <br />';
		print_r($this->config_tb_model->getList()->getData());
		echo '<br />last query is <br />';
		echo $this->config_tb_model->getLastQuery();
		echo '<br />';
		echo '<br />';

		$unit = $this->config->get('service/define/unit');
		echo 'Site Unit is '.$unit.'<br />';

        $urn = 'service/define/';
        $data = array(
                'unit' => '$'
                );
        

		$this->config->setup('service/define', $data);

		$unit = $this->config->get('service/define/unit');
		echo 'Site Unit is '.$unit.'<br />';

		//$this->_view('welcome_message');
	}


    public function blank() {
		$this->_view('blank');
    }
}
