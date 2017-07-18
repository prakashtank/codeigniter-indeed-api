<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    function __construct()
    {
            parent::__construct();
            $this->load->helper('url');
            $this->load->model('querycron');
    }
    
    public function index()
    {
        $publisher = '*********'; //Indeed Publisher ID
       
        $check_category = $this->querycron->check_category();
        
        if($check_category!='0')
        {
        
            $category = $this->querycron->get_category();
            foreach($category as $row_c)
            {
                $cate_id = $row_c['id'];
                $q = $row_c['name'];
                $cron = $row_c['cron']; //0 and //1
                $page = $row_c['page']; //dafault 1 set and if 1 to than 10 page in indeed
                if($page=='10')
                {
                    $update_data = array('page' => '1');
                    $this->querycron->update_page_id($cate_id,$update_data);
                }

            }
            
            if($page=='0')
            {
                $start = '0';
                $limit = '25';
            }
            else
            {
                $start = $page * 25;
                $limit = $start + 25;
            }
            
            $q = str_replace(' ', '%20', $q);
            /*$start = '0';
            $limit = '25';*/
           
            $userip = $_SERVER['REMOTE_ADDR'];
            $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? ($_SERVER['HTTP_USER_AGENT']) : 'unknown';
            $v = '2';

            //$url = $url."publisher=".$publisher."&q=".$q."&sort=".$sort."&start=".$start."&limit=".$limit."&co=".$co."&userip=".$userip."&useragent=".$useragent."&v=".$v."&format=json";

            $url_main = "http://api.indeed.com/ads/apisearch?publisher=$publisher&q=$q&sort=date&start=$start&limit=$limit&radius=&latlong=1&co=in&v=2&format=json";
            //echo '<br>';

            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url_main,));
            $resp = curl_exec($curl);
            $data = json_decode($resp, true);
            //echo $data['version'];
            //echo $data['results']['0']['jobtitle'];
            $lat = $long = $longitude = $latitude = "";
            foreach ($data['results'] as $row) {
                 
              $job_title =  $row['jobtitle'];
              $company = $row['company'];
              $city = $row['city'];
              $state = $row['state'];
              $country = $row['country'];
              if((isset($city)) && ($city !="")){ $city= $city.',';} else { $city = "";}
              if((isset($state)) &&($state !="")) { $state = $state.',';} else {$state="";}
              $location = $city.$state.$country;


              $date = $row['date'];
              $created_date = date("Y-m-d h:i:s", strtotime($date));
              $descripation = $row['snippet'];
              if(isset($row['latitude'])) {  $lat = $row['latitude'];} else { $lat = "";}
              if(isset($row['longitude'])){  $long = $row['longitude']; } else { $long ="";}
              $jobkey = $row['jobkey'];
              $url = $row['url'];
              $web  = "Indeed";
				
			   //checking jobkey already is exits
              $count = $this->querycron->checkjobs($jobkey);
               
              if($count=='0')
              {
                    $jobs['jobs'] = array('job_title' => $job_title,
                                    'in_company_name' => $company,
                                    'location' => $location,
                                    'created_date' => $created_date,
                                    'job_desc' => $descripation,
                                    'latitude' => $lat,
                                    'longitude' => $long,
                                    'url' => $url,
                                    'jobkey' => $jobkey,
                                    'api' => $web,
                                    'category' => $cate_id
                                    );
                  
            //insert job
            //var_dump($jobs['jobs']);

                  $this->querycron->insert_jobdata($jobs['jobs']);
              }
			}
            
            //all task done update page and cron
            $page_in = $page + 1;
            $up_last = array('cron' => '1', 'page' => $page_in);
            $data_return = $this->querycron->update_page_id($cate_id,$up_last);
			$this->load->view('cron/index.php');
			//end
        }
        else
        {
            //update category
            $update = array('cron' => '0');
			$data_return = $this->querycron->update_category($update);
			$this->load->view('cron/index.php');

        }
    }  
}
