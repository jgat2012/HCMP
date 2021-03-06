<?php
/**
 * @author Kariuki
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Reports extends MY_Controller 
{
	function __construct() 
	{
		parent::__construct();
		$this -> load -> helper(array('form', 'url'));
		$this -> load -> library(array('hcmp_functions', 'form_validation'));
	}


	public function index() 
	{
		$identifier = $this -> session -> userdata('user_indicator');

		switch ($identifier) {
			case moh :
				$data['content_view'] = "";
				$view = 'shared_files/template/dashboard_template_v';
				break;
		    case county :
			case district :
				$county_id = $this -> session -> userdata('county_id');
				$facility_id = $this -> session -> userdata('facility_id');
	            $data['district_data'] = districts::getDistrict($county_id);
	            $data['c_data'] = Commodities::get_all_2();
				$data['categories']=commodity_sub_category::get_all_pharm();
				$data['banner_text'] = "Stocking Levels";
				$data['title'] = "Stocking Levels";
				$data['content_view'] = "facility/facility_reports/reports_v";
				$view = 'shared_files/template/template';
				$data['report_view'] = "subcounty/reports/county_stock_level_filter_v";
				$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
				$data['active_panel']='stocking_levels';		
				break;
			case moh_user :
				$data['content_view'] = "";
				break;
			case facility_admin :
			case facility :
				$facility_code = $this -> session -> userdata('facility_id');
				$data['content_view'] = "facility/facility_reports/reports_v";
				$data['facility_code'] = $this -> session -> userdata('facility_id');
				$view = 'shared_files/template/template';
				$data['report_view'] = "facility/facility_reports/potential_expiries_v";
				$data['sidebar'] = "shared_files/report_templates/side_bar_v";
				$data['report_data'] = Facility_stocks::potential_expiries($facility_code);
                $data['active_panel']='expiries';
				break;
			case district_tech :
				$data['content_view'] = "";
				break;
			case rtk_manager :
				$data['content_view'] = "";
				break;
			case super_admin :
				$data['content_view'] = "";
				break;
			case allocation_committee :
				$data['content_view'] = "";
				break;
		}

		$data['title'] = "Reports";
		
		$this -> load -> view($view, $data);
	}

	/*
	 |--------------------------------------------------------------------------|
	 | SHARED REPORTS															|
	 |--------------------------------------------------------------------------|
	 */
	//Default function for all non functioning parts of the system
	public function work_in_progress()
	{
		$data['title'] = "Work In Progress";
		$data['banner_text'] = "Work In Progress";
		$data['content_view'] = "shared_files/work_in_progress";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
	}
	// get the commodity listing here
	public function commodity_listing() 
	{
		$data['title'] = "Commodity Listing";
		$data['banner_text'] = "Commodity Listing";
		$data['content_view'] = "shared_files/commodities/commodity_list_v";
		$data['commodity_list'] = commodity_sub_category::get_all();
		$this -> load -> view('shared_files/template/template', $data);
	}
   public function force_file_download(){
   	$this -> hcmp_functions -> download_file($this->input->get('url'));
   }

	public function get_facilities() {
		$district = $_POST['district'];
		$facilities = Facilities::getFacilities($district);
		$list = "";
		foreach ($facilities as $facilities) {
			$list .= $facilities -> facility_code;
			$list .= "*";
			$list .= $facilities -> facility_name;
			$list .= "_";
		}
		echo $list;
	}

	public function district_store_reports(){
	$district_id = $this -> session -> userdata('district_id');
	$data['content_view'] = "facility/facility_reports/reports_v";
	$view = 'shared_files/template/template';
	$data['report_view'] = "subcounty/reports/drug_store_reports_home";
	$data['sidebar'] = "subcounty/reports/drug_store_side_bar";
	$data['report_data'] = Facility_stocks::drug_store_potential_expiries($district_id);
    $data['active_panel']='expiries';
    $data['banner_text'] = "Drug Store Potential Expiries";
    $data['title'] = "District Store Reports";
		
		$this -> load -> view($view, $data);
	}

     /*
	 |--------------------------------------------------------------------------
	 | FACILITY REPORTS
	 |--------------------------------------------------------------------------
	 */
	 //facility user manual
	 public function get_manual()
	 {
	 	//$file_name = $facility_name . '_facility_user_manual_downloaded as'. date('d-m-y');
	 	$this -> hcmp_functions -> download_file(base_url('assets/manual/Manual.pdf'));
	 }
	
	///////////////////GET FACILITY STOCK DATA/////////////////////////////
	public function facility_evaluation_report()
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$user_id = $this -> session -> userdata('user_id');
		
		$evaluation = Facility_Evaluation::getAll($facility_code, $user_id) -> toArray();
	
		$data = (count($evaluation) == 0) ? 
		array(0 => array('fhead_no' => null, 'fdep_no' => null, 'nurse_no' => null, 
		'sman_no' => null, 'ptech_no' => null, 'trainer' => null, 'comp_avail' => null, 
		'modem_avail' => null, 'bundles_avail' => null, 'manuals_avail' => null, 
		'satisfaction_lvl' => null, 'agreed_time' => null, 'feedback' => null, 
		'pharm_supervision' => null, 'coord_supervision' => null, 'req_id' => null, 
		'req_spec' => null, 'req_addr' => null, 'train_remarks' => null, 
		'train_recommend' => null, 'train_useful' => null, 'comf_issue' => null, 
		'comf_order' => null, 'comf_update' => null, 'comf_gen' => null, 'use_freq' => null, 
		'freq_spec' => null, 'improvement' => null, 'ease_of_use' => null, 'meet_expect' => null, 
		'expect_suggest' => null, 'retrain' => null)) : $evaluation;
		
		$data['evaluation_data'] = $data;
		$data['facility_code'] = $facility_code;
		$data['user_id'] = $user_id;
		$data['facilities'] = Facilities::get_facility_name($facility_code);
		$data['title'] = "Facility Evaluation Form";
		$data['content_view'] = "facility/facility_reports/facility_evaluation";
		$data['banner_text'] = "Facility Evaluation Form";
		$this -> load -> view("shared_files/template/template", $data);
		
				
	}
	public function facility_impact_report()
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$report = Facility_Impact_Evaluation::get_all($facility_code, $current_user);
		isset($report)? $data['filled_report']=$report:$data['filled_report']=NULL;
		
		$data['facilities'] = Facilities::get_facility_name($facility_code);
		$data['title'] = "Facility Impact Report";
		$data['content_view'] = "facility/facility_reports/facility_impact_evaluation";
		$data['banner_text'] = "Facility Evaluation Form";
		$this -> load -> view("shared_files/template/template", $data);
		
		
	}
	public function save_facility_impact_evaluation() 
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$date = date('y-m-d');
		$save_time = date('Y-m-d H:i:s');
		
		//Get data from the form
		$personnel_no = $_POST['personnel_no'];
		$no_still_using_tool = $_POST['no_still_using_tool'];
		$trainee_cadre = $_POST['trainee_cadre'];
		$weekly_no_of_times = $_POST['weekly_no_of_times'];
		$no_of_commodity_stock_out = $_POST['no_of_commodity_stock_out'];
		$duration_of_stock_out = $_POST['duration_of_stock_out'];
		$total_expired_commodities = $_POST['total_expired_commodities'];
		$total_overstocked_commodities = $_POST['total_overstocked_commodities'];
		$date_of_last_order = date('Y-m-d',strtotime($_POST['date_of_last_order']));
		$quarter_served = $_POST['quarter_served'];
		$elaborated_discrepancies = $_POST['elaborated_discrepancies'];
		$expected_date_of_delivery = date('Y-m-d',strtotime($_POST['expected_date_of_delivery']));
		$general_challenges = $_POST['general_challenges'];
		//Values from checkboxes
		$qstn_tally = $_POST['qstn_tally'];
		$adequate_storage =$_POST['adequate_storage'];
		$discrepancies = $_POST['discrepancies'];
				
		$dbData = array('facility_code'=>$facility_code,
						'user_id'=>$current_user,
						'no_of_personnel'=> $personnel_no,
						'no_still_using_tool'=> $no_still_using_tool,
						'cadres_of_users'=> $trainee_cadre,
						'no_of_times_a_week'=> $weekly_no_of_times,
						'does_physical_count_tally'=> $qstn_tally,
						'amount_of_commodities_stocked'=> $no_of_commodity_stock_out,
						'duration_of_stockout'=> $duration_of_stock_out,
						'amount_of_expired_commodities'=> $total_expired_commodities,
						'amount_of_overstocked_commodities'=> $total_overstocked_commodities,
						'adequate_storage'=> $adequate_storage,
						'date_of_last_order'=> $date_of_last_order,
						'quarter_served'=> $quarter_served,
						'discrepancies'=> $discrepancies,
						'reasons_for_discrepancies'=> $elaborated_discrepancies,
						'date_of_delivery'=> $expected_date_of_delivery,
						'general_challenges'=> $general_challenges,
						'report_time'=> $save_time);
	
		$this ->db->insert('facility_impact_evaluation',$dbData);
		$this->facility_impact_report();
		
	}
	public function save_facility_evaluation() 
	{
		$facility_code =  $this -> session -> userdata('facility_id');
		$current_user = $this -> session -> userdata('user_id');
		$date = date('y-m-d');
		$data_array = $_POST['data_array'];
		$dataarray = explode("|", $data_array);
		$f_headno = $dataarray[0];
		$f_depheadno = $dataarray[1];
		$nurse_no = $dataarray[2];
		$store_mgrno = $dataarray[3];
		$p_techno = $dataarray[4];
		$trainer = $dataarray[5];
		$comp_avail = $dataarray[6];
		$modem_avail = $dataarray[7];
		$bundles_avail = $dataarray[8];
		$manuals_avail = $dataarray[9];
		$satisfaction_lvl = $dataarray[10];
		$agreed_time = $dataarray[11];
		$feedback = $dataarray[12];
		$pharm_supervision = $dataarray[13];
		$coord_supervision = $dataarray[14];
		$req_id = $dataarray[15];
		$req_spec = $dataarray[16];
		$req_addr = $dataarray[17];
		$train_remarks = $dataarray[18];
		$train_recommend = $dataarray[19];
		$train_useful = $dataarray[20];
		$comf_issue = $dataarray[21];
		$comf_order = $dataarray[22];
		$comf_update = $dataarray[23];
		$comf_gen = $dataarray[24];
		$use_freq = $dataarray[25];
		$freq_spec = $dataarray[26];
		$improvement = $dataarray[27];
		$ease_of_use = $dataarray[28];
		$meet_expect = $dataarray[29];
		$expect_suggest = $dataarray[30];
		$retrain = $dataarray[31];

		$mydata = array('facility_code' => $facility_code, 
						'assessor' => $current_user, 
						'date' => $date, 
						'fhead_no' => $f_headno, 
						'fdep_no' => $f_depheadno, 
						'nurse_no' => $nurse_no, 
						'sman_no' => $store_mgrno, 
						'ptech_no' => $p_techno, 
						'trainer' => $trainer, 
						'comp_avail' => $comp_avail, 
						'modem_avail' => $modem_avail, 
						'bundles_avail' => $bundles_avail, 
						'manuals_avail' => $manuals_avail, 
						'satisfaction_lvl' => $satisfaction_lvl, 
						'agreed_time' => $agreed_time, 
						'feedback' => $feedback, 
						'pharm_supervision' => $pharm_supervision, 
						'coord_supervision' => $coord_supervision, 
						'req_id' => $req_id, 
						'req_spec' => $req_spec, 
						'req_addr' => $req_addr, 
						'train_remarks' => $train_remarks, 
						'train_recommend' => $train_recommend, 
						'train_useful' => $train_useful, 
						'comf_issue' => $comf_issue, 
						'comf_order' => $comf_order, 
						'comf_update' => $comf_update, 
						'comf_gen' => $comf_gen, 
						'use_freq' => $use_freq, 
						'freq_spec' => $freq_spec, 
						'improvement' => $improvement, 
						'ease_of_use' => $ease_of_use, 
						'meet_expect' => $meet_expect, 
						'expect_suggest' => $expect_suggest, 
						'retrain' => $retrain);

		echo Facility_Evaluation::save_facility_evaluation($mydata);
		
		$facility_code = $this -> session -> userdata('facility_id');
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "facility/facility_reports/potential_expiries_v";
		$data['report_data'] = Facility_stocks::potential_expiries($facility_code);
		$data['active_panel']='expiries';
		$view = 'shared_files/template/template';
				

	}
	public function facility_evaluation_($message = NULL) 
	{
		$this -> session -> set_flashdata('system_success_message', "Facility Training Evaluation Has been " . $message);
		redirect('reports/facility_evaluation_report');
	}
	
	public function facility_stock_data() 
	{
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_stocks::get_distinct_stocks_for_this_facility($facility_code,"batch_data",'all');
		$data['title'] = "Facility Stock";
		$data['content_view'] = "facility/facility_reports/facility_stock_data_v";
		$data['banner_text'] = "Facility Stock";
		$this -> load -> view("shared_files/template/template", $data);

	
	}
	//facility user manual
	/*public function get_facility_user_manual()
	{
		$this -> hcmp_functions ->  download_file(assets/manual/HCMPV2_User_Guide);
		
	}*/

	// get the facility transaction data for ordering or quick analysis
	public function facility_transaction_data() {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_transaction_table::get_all($facility_code);
        $data['last_issued_data']=facility_issues::get_last_time_facility_issued($facility_code);
		$data['title'] = "Facility Stock Summary";
		$data['content_view'] = "facility/facility_reports/facility_transaction_data_v";
		$data['banner_text'] = "Facility Stock Summary";
		$this -> load -> view("shared_files/template/template", $data);
	}

	///////GET THE ITEMS A FACILITY HAS STOCKED OUT ON
	public function facility_stocked_out_items() {
		$facility_code = $this -> session -> userdata('facility_id');
		$data['facility_stock_data'] = facility_stocks::get_items_that_have_stock_out_in_facility($facility_code);
		$data['title'] = "Facility Stock Out Summary";
		$data['content_view'] = "facility/facility_reports/facility_stocked_out_items_v";
		$data['banner_text'] = "Facility Stock Out Summary";
		$this -> load -> view("shared_files/template/template", $data);
	}
    public function order_delivery($order_id) {
    	$test= $this -> hcmp_functions -> create_order_delivery_color_coded_table($order_id);
		$data['content_view'] = "facility/facility_orders/view_order_delivery_details_v";
		$data['table']=$test['table'];
		$data['order_id']=$order_id;
		$data['title'] = "Facility Order delivery Report";
		$data['banner_text'] = "Facility Order delivery Report";
		$this -> load -> view('shared_files/template/template', $data);
	}
	public function download_order_delivery($order_id) {
    	$test= $this -> hcmp_functions -> create_order_delivery_color_coded_table($order_id);
		$file_name = $test['facility_name'] . '_facility_order_no_' .$order_id. "_date_created_" .$test['date_ordered'];
		$pdf_data = array("pdf_title" => "Order Report For $test[facility_name]", 'pdf_html_body' => $test['table'], 'pdf_view_option' => 'view_file', 'file_name' => $file_name);
		$this -> hcmp_functions -> create_pdf($pdf_data);
	}

	public function order_listing($for,$report=null) 
	{
		$facility_code =$county_id=$district_id=null ;	
		
		if($for=='facility'):
			$facility_code = $this -> session -> userdata('facility_id');
			$template = "shared_files/template/template";		
			$desc = 'Facility Orders';
		elseif($for=='subcounty'):
			$template = "shared_files/template/dashboard_template_v";
			$district_id = $this -> session -> userdata('district_id');
			$desc = 'Subcounty Orders';
		elseif($for=='county'):
			$template = "shared_files/template/dashboard_template_v";
			$county_id = $this -> session -> userdata('county_id');
			$desc = 'County Orders';
		endif;		
		
		//get order information from the db
		$facility_order_count_ = facility_orders::get_facility_order_summary_count($facility_code, $district_id, $county_id);
		$facility_order_count = array();
		
		foreach ($facility_order_count_ as $facility_order_count_) 
		{
			$facility_order_count[$facility_order_count_['status']] = $facility_order_count_['total'];
		}
		
		$data['order_counts'] = $facility_order_count;
		$data['delivered'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "delivered");
		$data['pending'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "pending");
		$data['approved'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "approved");
		$data['rejected'] = facility_orders::get_order_details($facility_code, $district_id, $county_id, "rejected");
		$data['facilities']=($for=='subcounty') ? Facilities::get_facilities_all_per_district($this -> session -> userdata('district_id'),'set') : array();
		
		if($report=='true'):
			$data['title'] = "$desc Listing";
			$data['banner_text'] = "$desc Listing";
			$data['content_view'] = "facility/facility_reports/reports_v";
			$data['report_view'] = "facility/facility_orders/order_listing_v";
			$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
			$data['active_panel'] = "orders";
			if($this->input->is_ajax_request()):
				return $this -> load -> view("facility/facility_orders/order_listing_v", $data);
			endif;
		else:
		$data['title'] = $desc;
		$data['banner_text'] = $desc;
		$data['content_view'] = "facility/facility_orders/order_listing_v";	
		endif;
		
		$this -> load -> view('shared_files/template/template', $data);
	}

	public function create_excel_facility_stock_template() {
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
		$facility_stock_data = Commodities::get_facility_commodities($facility_code, 'sub category data');
		$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 'doc_title' => 'facility stock list template ', 'file_name' => 'facility stock list template');
		$row_data = array(); 
		$column_data = array("item HCMP code", "Description", "Supplier","Supplier ID", "Commodity Code", "Unit Size",
		"Total Units", "Batch No", "Manufacturer", "Expiry Date (month-year)", "Stock Level (Pack)", "Stock Level (Units)");
		$excel_data['column_data'] = $column_data;
		foreach ($facility_stock_data as $facility_stock_data_item) :
		array_push($row_data, array($facility_stock_data_item["commodity_id"],
		$facility_stock_data_item["commodity_name"],
		$facility_stock_data_item["source_name"],
		$facility_stock_data_item["supplier_id"],
		$facility_stock_data_item["commodity_code"],
		$facility_stock_data_item["unit_size"], 
		$facility_stock_data_item["total_commodity_units"], 
		"", "", "", "0", "0"));
		endforeach;
		$excel_data['row_data'] = $row_data;

		$this -> hcmp_functions -> create_excel($excel_data);
	}
    
	public function create_excel_facility_order_template($order_id,$facility_code) {

		$this -> hcmp_functions -> clone_excel_order_template($order_id,'download_file');
	}

    public function aggragate_order_new_sorf($order_id){
    $order_id_array=explode("_", $order_id);
	$facility_name=array();
	$order_id=array();
	$order_total=array("","","","");
	$order_total_all=0;
	$order_total_all_items=0;
	foreach($order_id_array as $single_order_id){//get the facility names from the orders 
		if($single_order_id>0):
	    $test=facility_orders::get_order_($single_order_id);
	    $order_id=array_merge($order_id,array($single_order_id));
		foreach($test as $test_){
		$order_total=array_merge($order_total,array("",$test_->order_total));
		$order_total_all=$order_total_all+$test_->order_total;
		foreach($test_->facility_detail as $facility_data){
		array_push($facility_name,"$facility_data->facility_name",'');
		}		
		}
		endif;
	 }
	    array_push($order_total,$order_total_all);
	    array_push($facility_name ,"TOTAL");//combine all of them
        $stock_data=Commodities::get_all_from_supllier(1);
		$from_stock_data=count($stock_data);//get items from a supplier 
		$excel_data = array('doc_creator' => "HCMP", 'doc_title' => 'test ', 'file_name' => 'test');
		$row_data = array(array("Product Code","Item description(Name/form/strength)","Order unit size", "Price"));
		foreach($order_id as $order_id_){
		array_push($row_data[0],"Quantity  to Order","Total");	//push this to go with each facility
		}		
		$column_data = array("","","FACILITY NAME","");
		$column_data=array_merge($column_data ,$facility_name);
		$excel_data['column_data'] = $column_data;
		for($i=0;$i<$from_stock_data;$i++):
		$total_all=0;
	    $temp_array=array();
	    $temp_array_=array();
		if ($i==0) {//push the first sub category		
		array_push($row_data,
		 array($stock_data[$i]["sub_category_name"],"","", "",  ""));
			   }      	
        else if( $stock_data[$i]['sub_category_name']!=$stock_data[$i-1]['sub_category_name']){//push the first sub category
       	 array_push($row_data,array($stock_data[$i]["sub_category_name"],"","", "",  ""));
       	 }
		foreach($order_id as $order_id_){
		$total=facility_order_details::get_order_details_from_order($order_id_,$stock_data[$i]["commodity_id"]);
			    
		if(count($total)==0){
		 array_push($temp_array,0,0);
		}else{
		$total_=$total[0]['total']*str_replace(",", '',$stock_data[$i]["unit_cost"]);
		array_push($temp_array,$total[0]['total'],$total_);
		$total_all=$total_all+$total_;
		$order_total_all_items=$order_total_all_items+$total_;
		}
		}	
        $temp_array_= array($stock_data[$i]["commodity_code"],
		 $stock_data[$i]["commodity_name"], 
		 $stock_data[$i]["unit_size"], 
		 $stock_data[$i]["unit_cost"]);
		 $temp_array_=array_merge($temp_array_,$temp_array);
		 array_push($temp_array_,$total_all);
		 array_push($row_data,$temp_array_);
		endfor;

        array_push($row_data,$order_total);
		$excel_data['row_data'] = $row_data;
      
		$this -> hcmp_functions -> create_excel($excel_data);    	
    }

	public function expiries($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['title'] = "Expiries";
		$data['banner_text'] = "Expiries";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['expiry_data'] = Facility_stocks::All_expiries($facility_code,'all');
		$data['report_view'] = "facility/facility_reports/expiries_v";
        $data['active_panel']='expiries';
        //echo "<pre>";print_r($data);echo "</pre>";exit;
		$this -> load -> view("shared_files/template/template", $data);

	}

		public function store_expiries() {
        $district_id=isset($district_id) ? $district_id: $this -> session -> userdata('district_id');
		$district_name=Districts::get_district_name($district_id)->toArray();
		$data['district_name']=$district_name[0]['district'];
		$data['title'] = "Store Expiries";
		$data['banner_text'] = "Drug Store Expiries";
		$data['sidebar'] = "subcounty/reports/drug_store_side_bar";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['expiry_data'] = Facility_stocks::drug_store_commodity_expiries($district_id);
		$data['report_view'] = "facility/facility_reports/expiries_v";
        $data['active_panel']='expiries';

		$this -> load -> view("shared_files/template/template", $data);

	}

	public function expiry_tracking($facility_code=null){
		$years = array();
		$month_names[] = array();

		$years[0] = date('Y');
		$years[1] =date('Y') +1;
		$years[2] = date('Y') +2;

		$month_names[0] = 'January';
		$month_names[1] = 'February';
		$month_names[2] = 'March';
		$month_names[3] = 'April';
		$month_names[4] = 'May'; 
		$month_names[5] = 'June';
		$month_names[6] = 'July';
		$month_names[7] = 'August';
		$month_names[8] = 'September';
		$month_names[9] = 'October';
		$month_names[10] = 'November';
		$month_names[11] = 'December';

		$facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$expiry_data = Facility_stocks::expiries_report($facility_code);

		$data['current_year_months'] = $current_year_months;
		$facility = $this -> session -> userdata('facility_id');
		$user_id = $this -> session -> userdata('user_id');
		$user_names = Users::get_user_names($user_id);
		$data['user_names'] = ($user_names[0]['fname']." ".$user_names[0]['lname']);

		$data['current_year'] = date('Y');
		$data['month_names'] = $month_names;

		$facility_info = tb_data::get_facility_name($facility);
		$facility_district = $facility_info['district'];
		$district_name_ = Districts::get_district_name_($facility_district);
		$district_name = $this -> session -> userdata('district');
		$data['years'] = $years;
		$data['facility_code'] = $facility_info['facility_code']; 
		$data['district_region_name'] = $district_name_['district'];
		$data['facility_name'] = ($facility_info['facility_name']);
		$data['facility_type_'] = ($facility_info['owner']);
		$data['expiry_data'] = Facility_stocks::expiries_report($facility_code);

		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['title'] = "Expiriy Tracking Chart";
		$data['banner_text'] = "Expiriy Tracking Chart";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "facility/facility_reports/expiries_tracking_chart";
        $data['active_panel']='expiries';
		$this -> load -> view("shared_files/template/template", $data);

	}

	public function potential_exp_process($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];;
		$interval = $_POST['option_selected'];
		$data['report_data'] = Facility_stocks::specify_period_potential_expiry($facility_code, $interval);

		$this -> load -> view("facility/facility_reports/ajax/potential_expiries_ajax", $data);

	}

	public function potential_exp_process_store($district_id=null) {
        $district_id=isset($district_id) ? $district_id: $this -> session -> userdata('district_id');
		$district_name=Districts::get_district_name($district_id)->toArray();
		$data['district_name']=$district_name[0]['district'];
		$interval = $_POST['option_selected'];
		$data['report_data'] ;
		$echo = Facility_stocks::specify_period_potential_expiry_store($district_id, $interval);
		
		// echo "<pre>";print_r($echo);echo "</pre>";exit;

		return $this -> load -> view("facility/facility_reports/ajax/potential_expiries_ajax", $data);

	}

	public function potential_exp_process_subcounty($facility_code=null) {
        $facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['facility_code']=$facility_code;
		$data['title'] = "Potential Expiries";
		$data['banner_text'] = "Potential Expiries";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['active_panel']='expiries';
		$data['report_view'] = "facility/facility_reports/potential_expiries_v";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['report_data'] = Facility_stocks::potential_expiries($facility_code);
        $this -> load -> view($view, $data);
	}

	public function stock_control($facility_code=null) {
		$facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];
		$data['title'] = "Bin Card";
		$data['banner_text'] = "Bin Card";
		$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v": "shared_files/report_templates/side_bar_v" ;
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['commodities']=Commodities::get_facility_commodities($facility_code);
		$data['report_view'] = "facility/facility_reports/bin_card_v";
		$data['active_panel'] = (!$this -> session -> userdata('facility_id')) ? "consumption": "stock_control_card" ;
		$this -> load -> view("shared_files/template/template", $data);

	}
	public function stock_control_ajax() {
		$facility_code = $this -> session -> userdata('facility_id');
		$commodity_id = $_POST['commodity_select'];
		$to = $_POST['to'];
		$from = $_POST['from'];
		$commodity_name = $_POST['commodity_name'];


		//to enable pdf download
		$data['commodity_id'] =$commodity_id;
		$data['commodity_name'] =$commodity_name;
		$data['from'] =$from;
		$data['to'] =$to;
		$data['facility_code']= $this -> session -> userdata('facility_id');
		$data_=	Facility_issues::get_bin_card($facility_code,$commodity_id,$from,$to);	
		$data['bin_card'] =$data_ ;
		$count_records=count($data);
		
		if ($count_records<=0) {

			echo ' <div class="" id="reports_display" style="min-height: 350px;" >
            <div style="margin:auto; text-align: center">
                
                <h2>You have no Records, for the given filters</h2>
                <h3>
                  Please select filters above.
                </h3>
                
                </div>
            </div>
            ';
		}else{
		$this -> load -> view("facility/facility_reports/ajax/bin_card_ajax", $data);

		}

	}
	//Gets the consumption data for a particular facility
	public function view_consumption()
	{
		$data['report_view']="facility/facility_reports/consumption_report_v";
		$data['report_title'] =	"Divisional Malaria Reports";
	}
	public function load_expiries() 
	{
        $facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$expired_commodities = Facility_stocks::get_expiries($facility_code);
		
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Expiries in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Expiries  (values in units)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Expiries"=>array())));
		
		foreach($expired_commodities as $facility_stock_expired):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['month']));	
			$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total_expiries']));	
		endforeach;
		
		$faciliy_expiry_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
		
		$data['title'] = "Facility Expiries";
		$data['banner_text'] = "Facility Expiries";
		$data['graph_data'] = $faciliy_expiry_data;
       	$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view'] = "facility/facility_reports/ajax/facility_expiry_filter_v";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['active_panel']='statistics';
		$this -> load -> view($view, $data);
        
		
	}
	public function filter_expiries($year = null, $month = null, $district_id = null, $option = null, $facility_code = null,$report_type=null)
	{
		$year = strtolower($year);
		$month = strtolower($month);
		
		$option=($option=="NULL") ? "units" :$option;
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Expiries in '.$facility_name));
		
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Expiries (values in '.$option.')'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Expiries"=>array())));
		//get the filtered expiries
		if(($year != 'null') && $month != 'null' && $month>0)
		{
			//When both Month and year are set
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, $month,$option ,"all_");
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['name']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		
		}elseif($year == 'null' && $month != 'null' && $month>0)
		{
			//When only month is set
			$year = date("Y");	
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, $month,$option ,"all_"); 
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['name']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		}
		elseif($month == 'null' && $year != 'null')
		{
			//When only year is set
			$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, null,$option ,"all"); 
			
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['cal_month']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach;
		}else if ($month == 'null' && $year == 'null'){
			$year = date("Y");
			$graph_data = array_merge($graph_data,array("graph_type"=>'line')); 
			
			$expired_commodities = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,
			$county_id, $year, null,$option ,"all");
			$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
			foreach($expired_commodities as $facility_stock_expired):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_stock_expired['cal_month']));	
				$graph_data['series_data']['Expiries'] = array_merge($graph_data['series_data']['Expiries'],array((int)$facility_stock_expired['total']));	
			endforeach; 
		}
		
	
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
		
	}
	public function load_cost_of_orders()
	{
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$cost_of_orders = facility_orders::get_cost_of_orders($facility_code);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Cost of Orders in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Cost of Orders (Order Value)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Cost"=>array())));
		
		foreach($cost_of_orders as $facility_cost_of_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_cost_of_orders['mwaka']));	
			$graph_data['series_data']['Total Cost'] = array_merge($graph_data['series_data']['Total Cost'],array((int)$facility_cost_of_orders['order_total']));	
		endforeach;
		
		$faciliy_data = $this->hcmp_functions->create_high_chart_graph($graph_data);
		
		$data['title'] = "Cost of Orders";
		$data['banner_text'] = "Cost of Orders";
		$data['graph_data'] = $faciliy_data;
       	$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view'] = "facility/facility_reports/ajax/cost_of_orders_filter";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
        
	}
	
	public function load_filtered_cost_of_orders($year=null)
	{
		$year = (isset($year)) ? $year: date("Y");
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];
		
		$cost_of_orders = facility_orders::get_cost_of_orders($facility_code, $year);
		//Holds all the months of the year
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Cost of Orders in '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Cost of Orders (Order Value)'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Cost"=>array())));
		
		foreach($cost_of_orders as $facility_cost_of_orders):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_cost_of_orders['mwaka']));	
			$graph_data['series_data']['Total Cost'] = array_merge($graph_data['series_data']['Total Cost'],array((int)$facility_cost_of_orders['order_total']));	
		endforeach;
		
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
		
        
	}
	//Gets the consumption data for a particular facility
	//Only the facility that is logged in
	public function consumption() 
	{
		$facility_code = $this -> session -> userdata('facility_id'); 
		$data['title'] = "Consumption"	;
		$data['banner_text'] = "Facility Consumption"	;
		$data['c_data'] = Commodities::get_facility_commodities($facility_code);
		$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view']="facility/facility_reports/ajax/consumption_stats_ajax";
		$data['content_view']="facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['active_panel']='consumption';
		$this -> load -> view($view, $data);
		
	}	
	public function filtered_consumption($commodity_id = null, $option = null,$from = null,$to = null,$report_type = null)
	{

		$year = (isset($year) && ($year>0))? $year: date("Y");
		$option = (isset($option)) ? $option: "units";

		$year = ($year == 0)? date("Y"):$year ;
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name_($facility_code)->toArray();
		$facility_name = $facility_name[0]['facility_name'];

		$consumption = Facility_stocks::get_facility_consumption_level_new($facility_code, $commodity_id, $year, $option);

		//Build the line graph showing the expiries graph
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Consumption for '.$facility_name));
		$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Consumption'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Consumption"=>array())));
		
		
		if($option == "service_point")
		{
			$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
			$consumption = Facility_stocks::get_facility_consumption_level_new($facility_code, $commodity_id, $year, $option);

			foreach($consumption as $facility_consumption):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_consumption['service_name']));	
				$graph_data['series_data']['Consumption'] = array_merge($graph_data['series_data']['Consumption'],array((int)$facility_consumption['total_consumption']));	
			endforeach;
		}else{
			$graph_data = array_merge($graph_data,array("graph_type"=>'line'));

			//$consumption = Facility_stocks::get_facility_consumption_level_new($facility_code, $commodity_id, $year, $option);
			$consumption = Facility_stocks::get_filtered_commodity_consumption_level($facility_code, $commodity_id, $year, $option);
			foreach($consumption as $facility_consumption):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_consumption['month']));	
				$graph_data['series_data']['Consumption'] = array_merge($graph_data['series_data']['Consumption'],array((int)$facility_consumption['total_consumption']));	
			endforeach;
		}
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
	}

	public function order_report()
	{
		$data['title'] = "Facility Orders"	;
		$data['banner_text'] = "Facility Orders"	;
		$data['graph_data'] = $facility_order_data;
		$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['report_view']="facility/facility_reports/ajax/facility_orders_filter_v";
		$data['content_view']="facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['active_panel']='orders';
		$this -> load -> view($view, $data);
		
	}

	public function filter_facility_orders($facility_code, $year, $month, $option)
	{
		
		$facility_code = isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$facility_name = Facilities::get_facility_name2($facility_code);
		
		//Build the line graph showing the expiries graph
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'graph-section'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Total Orders for '.$facility_name['facility_name'].' in '.$m.' '. $year));
		$graph_data = array_merge($graph_data,array("graph_type"=>'column'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'Total Orders (values in '.$option.')'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>array("Total Orders"=>array())));
		
		if ($year ==0 && $month == 0)
		{
			//Where the month and year have not been selected
			//$year = date("Y");
			$orders = facility_orders::get_facility_orders($facility_code, $year);
			$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
			foreach($orders as $facility_orders):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['month']));	
				$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total']));	
			endforeach;
			
		}
		elseif($year ==0 && $month != 0)
		{
			//When only the month is selected
			//$year = date("Y");
			$m = date('F',strtotime('2000-'.$month.'-01'));
			$orders = facility_orders::get_filtered_facility_orders($facility_code, $year, $month, $option);
			
			foreach($orders as $facility_orders):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['name']));	
				$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total']));	
			endforeach;
			
		}
		elseif($year !=0 && $month == 0)
		{
			//When the $year is not set but the month is
			//$year = date("Y");
			$orders = facility_orders::get_filtered_facility_orders($facility_code, $year,$month, $option);
			foreach($orders as $facility_orders):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['name']));	
				$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total']));	
			endforeach;
		}
		else{
			//Where both month and year are selected by the user
			//Get the name of the month selected
			$m = date('F',strtotime('2000-'.$month.'-01'));
			$orders = facility_orders::get_filtered_facility_orders($facility_code, $year, $month, $option);
			
			foreach($orders as $facility_orders):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'],array($facility_orders['name']));	
				$graph_data['series_data']['Total Orders'] = array_merge($graph_data['series_data']['Total Orders'],array((int)$facility_orders['total']));	
			endforeach;
		}
	
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		
	}

	public function get_facility_json_data($district_id) {
		echo json_encode(facilities::get_facilities_which_are_online($district_id));
	}

	public function get_facility_json($district) {
	echo json_encode(Facilities::getFacilities_json($district));
				
	}
	public function get_sub_county_json_data($county_id) {
		echo json_encode(Districts::get_districts($county_id));
	}
	public function get_counties_json_data() {
		echo json_encode(Counties::getAll());
	}
	     

	 //For system uptake option on SUB-COUNTY dashboard
	 public function get_sub_county_facility_mapping_data($year = null, $month = NULL) 
	 {
	 	$year = (isset($year)&& ($year>0))? $year : date("Y");
		$month = (isset($month)&& ($month>0))? $month : date("m");
		$identifier = $this -> session -> userdata('user_indicator');
		$county_id = $this -> session -> userdata('county_id');
		
		//Get facility code
		$facility_code = $this -> session -> userdata('facility_id');
		$facility_code_ = (isset($facility_code)&&($facility_code>0) )? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
			
		$district_id = $this -> session -> userdata('district_id');
		$district = $this -> session -> userdata('district_id');
		//Get the name of the county

		$county_name = Counties::get_county_name($county_id);
		$county_name = $county_name;
		// echo $county_id."<pre>";echo $county_name;echo "</pre>";exit;
		//get the name of the district
		$district_name_ = (isset($district_id)&&($district_id>0) )?Districts::get_district_name_($district_id):null;
		$district_name = $district_name_['district'];
		
		$first_day_of_the_month = date("Y-m-1", strtotime(date($year . "-" . $month)));
		$last_day_of_the_month = date("Y-m-t", strtotime(date($year . "-" . $month)));

		$date_1 = new DateTime($first_day_of_the_month);
		$date_2 = new DateTime($last_day_of_the_month);


		$facility_data = Facilities::get_Facilities_using_HCMP($county_id,$district);
		
		//echo "<pre>";print_r($facility_data);echo "</pre>";exit;
		$series_data = array();
		$category_data = array();
		$series_data_monthly = array();
		$category_data_monthly = array();
		$seconds_diff = strtotime($last_day_of_the_month) - strtotime($first_day_of_the_month);
		$date_diff = floor($seconds_diff / 3600 / 24);	
		

		switch ($identifier):
		case 'county':
			$graph_category_data = $facility_data;
			$graph_title = $county_name['county']." County ";
			
		break;
		case 'facility_admin':
		case 'facility':
			$graph_category_data = $facility_data;
			$graph_title = $facility_name;
			
		break;
		case 'district':
			
			$graph_category_data = $facility_data;
			$graph_title = $district_name." SubCounty ";
		break;	
		endswitch;
		
		for ($i = 0; $i <= $date_diff; $i++) :
			$day = 1 + $i;
			$new_date = "$year-$month-" . $day;
				
			$new_date = date('Y-m-d', strtotime($new_date));

			if (date('N', strtotime($new_date)) < 6) 
			{
				$date_ = date('D d', strtotime($new_date));
				$category_data = array_merge($category_data, array($date_));
				$temp_1 = array();
				foreach ($graph_category_data as $facility_) :
					$facility_id = $facility_ -> facility_code;
					$facility_name = $facility_ -> facility_name;
					$subcounty_data = Log::get_subcounty_login_count($county_id, $district_id,$facility_id, $new_date);
					(array_key_exists($facility_name, $series_data)) ? 
					$series_data[$facility_name] = array_merge($series_data[$facility_name], array((int)$subcounty_data[0]['total'])) :
					$series_data = array_merge($series_data, array($facility_name => array((int)$subcounty_data[0]['total'])));
	
				endforeach;

			} else {
				// do nothing
			}
		endfor;
		
		
		//for setting the month name in the graph when filtering
		$m = date('F',strtotime('2000-'.$month.'-01'));
				
		$graph_data_daily = array();
		$graph_data_daily = array_merge($graph_data_daily,array("graph_id"=>'container'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_title"=>'Daily Facility Access log for '.$m." for ".$graph_title));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_type"=>'line'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_yaxis_title"=>'log In'));
		$graph_data_daily = array_merge($graph_data_daily,array("graph_categories"=>array()));
		$graph_data_daily = array_merge($graph_data_daily,array("series_data"=>$series_data));
	    $graph_data_daily['graph_categories'] = $category_data;	
		$graph_daily = $this->hcmp_functions->create_high_chart_graph($graph_data_daily);

		for ($i = 0; $i < 12; $i++) :
			$day = 1 + $i;
			//changed it to be a month
			$new_date = "$year-$day";
			$new_date = date('Y-m', strtotime($new_date));
			$date_ = date('M', strtotime($new_date));
			$category_data_monthly = array_merge($category_data_monthly, array($date_));

			foreach ($graph_category_data as $facility_) :
				$facility_id = $facility_ -> facility_code;
				$facility_name = $facility_ -> facility_name;
				$subcounty_data = Log::get_subcounty_login_monthly_count($county_id, $district_id,$facility_id, $new_date);

				(array_key_exists($facility_name, $series_data_monthly)) ? $series_data_monthly[$facility_name] = array_merge($series_data_monthly[$facility_name], array((int)$subcounty_data[0]['total'])) : $series_data_monthly = array_merge($series_data_monthly, array($facility_name => array((int)$subcounty_data[0]['total'])));
			endforeach;

		endfor;
		
		$graph_data = array();
		$graph_data = array_merge($graph_data,array("graph_id"=>'container_monthly'));
		$graph_data = array_merge($graph_data,array("graph_title"=>'Monthly Facility Access for '. $year));
		$graph_data = array_merge($graph_data,array("graph_type"=>'line'));
		$graph_data = array_merge($graph_data,array("graph_yaxis_title"=>'log In'));
		$graph_data = array_merge($graph_data,array("graph_categories"=>array()));
		$graph_data = array_merge($graph_data,array("series_data"=>$series_data_monthly));
	    $graph_data['graph_categories'] = $category_data_monthly;	
		$graph_monthly = $this->hcmp_functions->create_high_chart_graph($graph_data);

		
		$graph_log_data = array();
		$graph_log_data = array_merge($graph_log_data,array("graph_id"=>'log_data_graph'));
		$graph_log_data = array_merge($graph_log_data,array("graph_title"=>'User Activity for  '.$m.' for '. $graph_title));
		$graph_log_data = array_merge($graph_log_data,array("graph_type"=>'column'));
		$graph_log_data = array_merge($graph_log_data,array("graph_yaxis_title"=>'Frequency'));
		$graph_log_data = array_merge($graph_log_data,array("graph_categories"=>array('Decommissions','Redistributions','Stock Updates',
		'Orders','Issues','Log Ins')));
		$graph_log_data = array_merge($graph_log_data,array("series_data"=>array('Total %'=>array())));
		$log_data = Log::get_log_data($facility_code,$district_id,$county_id, $year, $month);
		$log_data_login_only = Log::get_login_only($facility_code,$district_id,$county_id, $year, $month);
		foreach($log_data as $log_data_)
		{

			$sum = $log_data_['user_log']+$log_data_login_only[0]['total'];
			$issues = ($log_data_['total_issues']/$sum)*100;
			$orders = ($log_data_['total_orders']/$sum)*100;
			$decommissions = ($log_data_['total_decommisions']/$sum)*100;
			$redistributions = ($log_data_['total_redistributions']/$sum)*100;
			$stock = ($log_data_['total_stock_added']/$sum)*100;;
			$user = ($log_data_login_only[0]['total']/$sum)*100;;
			$temp=array((int)$decommissions,(int)$redistributions,(int)$stock,(int)$orders,(int)$issues,(int)$user);
			$graph_log_data['series_data']['Total %'] = array_merge($graph_log_data['series_data']['Total %'],$temp);		
		}

		
		$graph_log = $this->hcmp_functions->create_high_chart_graph($graph_log_data);
			
		$data['graph_data_monthly'] = $graph_monthly;
		$data['graph_data_daily'] =	$graph_daily;
		$data['graph_log'] = $graph_log;
		
		$data['get_facility_data'] = facilities::get_facilities_online_per_district($county_id);
		$get_dates_facility_went_online = facilities::get_dates_facility_went_online($county_id);
		$data['data'] = $this -> get_county_facility_mapping_ajax_request("on_load");
		
		// echo "<pre>";print_r($data);echo "</pre>";exit;
		if($this->input->is_ajax_request()):
			
			return $this -> load -> view('subcounty/ajax/facility_roll_out_at_a_glance_v', $data);
		else:
			$data['title'] = "User Logs";
			$data['banner_text'] = "System Use Statistics";
		    $data['report_view'] = "subcounty/ajax/facility_roll_out_at_a_glance_v";		
			$data['sidebar'] = (!$this -> session -> userdata('facility_id')) ? "shared_files/report_templates/side_bar_sub_county_v":"shared_files/report_templates/side_bar_v";
			$data['content_view'] = "facility/facility_reports/reports_v";
			$data['active_panel'] =(!$this -> session -> userdata('facility_id')) ? "system_usage": "system_usage";
			$view = 'shared_files/template/template';
			$this -> load -> view($view, $data);
		
		endif;
		

	}
	
	public function get_county_facility_mapping_ajax_request($option = null) 
	{
		//$district_id = $this -> session -> userdata('district_id');
		$county_id = $this -> session -> userdata('county_id');
		//echo $county_id;
		$district_data = districts::getDistrict($county_id);
		$table_data = "<tbody>";
		$table_data_summary = "<tbody>";
		$district_names = "<thead><tr><th>Monthly Activities</th>";
		
		//Total number of facilities using HCMP in the district
		$district_total = array();
		//Total number of facilities in the district
		$district_total_facilities = array();
		//Total number of facilities targetted in the district
		$district_total_facilities_targetted = array();
		$district_total_facilities_using_hcmp = array();
		
		$table_district_totals = "";
		$all_facilities = 0;
		$total_using_hcmp = 0;
		$total_facility_list = '';
		$total_facilities_in_county = 0;
		$total_facilities_targetted = 0;
		$percentage_coverage = "";
		$percentage_coverage_total = 0;
		$percentage_coverage_using = "";
		$percentage_coverage_total_using = 0;
		
		$get_dates_facility_went_online = facilities::get_dates_facility_went_online($county_id);
		
		
		foreach ($get_dates_facility_went_online as $facility_dates) :

			$monthly_total = 0;
			$date = $facility_dates['date_when_facility_went_online'];
			$table_data .= "<tr>
	    <td>" . $date . "</td>";
			foreach ($district_data as $district_detail) :
				
				$district_id = $district_detail -> id;		
				$district_name = $district_detail -> district;
				$get_facilities_which_went_online_ = facilities::get_facilities_which_went_online_($district_id, $facility_dates['date_when_facility_went_online']);
				
				$total = $get_facilities_which_went_online_[0]['total'];
				$total_facilities = $get_facilities_which_went_online_[0]['total_facilities'];
				$total_facilities_targetted = $get_facilities_which_went_online_[0]['total_facilities_targetted'];
				$total_facilitites_using_hcmp = $get_facilities_which_went_online_[0]['total_using_hcmp'];
				
				
				
				$monthly_total = $monthly_total + $total;
				$all_facilities = $all_facilities + $total;
				(array_key_exists($district_name, $district_total)) ? $district_total[$district_name] = $district_total[$district_name] + $total : 
							$district_total = array_merge($district_total, array($district_name => ($total)));
				(array_key_exists($district_name, $district_total_facilities)) ? $district_total_facilities[$district_name] = $total_facilities : 
				$district_total_facilities = array_merge($district_total_facilities, array($district_name => $total_facilities));
				(array_key_exists($district_name, $district_total_facilities_targetted)) ? $district_total_facilities_targetted[$district_name] = $total_facilities_targetted : 
				$district_total_facilities_targetted = array_merge($district_total_facilities_targetted, array($district_name => $total_facilities_targetted));
				(array_key_exists($district_name, $district_total_facilities_using_hcmp)) ? $district_total_facilities_using_hcmp[$district_name] = $total_facilitites_using_hcmp : 
				$district_total_facilities_using_hcmp = array_merge($district_total_facilities_using_hcmp, array($district_name => $total_facilitites_using_hcmp));
				
				$table_data .= ($total > 0) ? "<td><a href='#' id='$district_id' class='ajax_call2 link' date='$date'> $total</a></td>" : "<td>$total</td>";
				 
			endforeach;
	
			$table_data .= "<td>$monthly_total</td></tr>";

		endforeach;
		
		$table_data .= "<tr>";
		$table_data_summary .= "<tr>";

		$checker = 1;
		foreach ($district_total as $key => $value) :
			$coverage = 0;
			$using = 0;
			@$coverage = round((($value / $district_total_facilities[$key])) * 100, 1);
			@$using_percentage = round((($district_total_facilities_targetted[$key]/$value)) * 100, 1);
			$percentage_coverage_total = $percentage_coverage_total + $coverage;
			$percentage_coverage_total_using = $percentage_coverage_total_using + $using_percentage;

			$district_names .= "<th>$key</th>";

			$total_facility_list .= ($checker == 1) ? "<tr><td><b>TOTAL: Facilities in District</b></td><td>$district_total_facilities[$key]</td>" : "<td>$district_total_facilities[$key]</td>";
			$table_data .= ($checker == 1) ? "<td><b>TOTAL: Facilities using HCMP</b></td><td>$value</td>" : "<td>$value</td>";
			$table_summary .= ($checker == 1) ? "<td><b>TOTAL: Facilities using HCMP</b></td><td>$value</td>" : "<td>$value</td>";
			
			$total_targetted_facility_list .= ($checker == 1) ? "<tr><td><b>TOTAL: Targetted Facilities in District</b></td><td>$district_total_facilities_targetted[$key]</td>":"<td>$district_total_facilities_targetted[$key]</td>";

			$total_facilities_in_county = $total_facilities_in_county + $district_total_facilities[$key];
			$targetted_total = $targetted_total + $district_total_facilities_targetted[$key];
			$total_using_hcmp = $total_using_hcmp + $district_total_facilities_using_hcmp[$key];
			
			
			@$targetted_vs_using_hcmp = round((($total_facilitites_using_hcmp /$total_facilities_targetted )) * 100, 1);
			@$final_coverage_total = round((($all_facilities / $total_facilities_in_county)) * 100, 1);
			$percentage_coverage_using .= ($checker == 1) ? "<tr><td><b>TOTAL: Targetted vs Using HCMP%</b></td>
			<td>$targetted_vs_using_hcmp %</td>" : "<td>$using_percentage %</td>";
			
			$percentage_coverage .= ($checker == 1) ? "<tr><td><b>% Coverage</b></td>
			<td>$coverage %</td>" : "<td>$coverage %</td>";
			$checker++;

		endforeach;
		$list_url=base_url().'reports/list_facilities';
		$table_data .= "<td><a href='#' id='total' class='ajax_call1 link' option='total' date='total'>$all_facilities</a></td></tr></tbody>";
		$table_data_summary .= "<td><a href='#' id='total' class='ajax_call2 link' date='total'>$all_facilities</a></td></tr></tbody>";
		$table_datas_summary .= "<td><a href='$list_url' id='total' class='ajax_call1 link' date='total'>$all_facilities</a></td>";
		$district_names .= "<th>TOTAL</th></tr></thead>";
		
		$final_coverage_total = 0;
		$targetted_vs_using_hcmp = 0;
		@$final_coverage_total = round((($all_facilities / $total_facilities_in_county)) * 100, 1);
		@$targetted_vs_using_hcmp = round((($targetted_total/$all_facilities)) * 100, 1);
		 
		$data_ = "
		<div class='tabbable tabs-left'>
		<div class='tab-content'>
        <ul class='nav nav-tabs'>
        <li class='active'><a href='#A' data-toggle='tab'>Monthly Break Down</a></li>
        <li class=><a href='#B' data-toggle='tab'>Roll out Summary</a></li>
        </ul>
         <div  id='A' class='tab-pane fade active in'>
			<table class='row-fluid table table-hover table-bordered table-update' width='80%' id='test1'>" 
			. $district_names . $table_data . $total_facility_list .  "<td>$total_facilities_in_county</td></tr>" 
			.$total_targetted_facility_list."<td>$targetted_total</td>" 
			. $percentage_coverage. "<td>$final_coverage_total %</td></tr>".$percentage_coverage_using."<td>$targetted_vs_using_hcmp %</td></tr>
			</table>
		</div>
		
		<div id='B' class='tab-pane fade' >
		<table class='row-fluid table table-hover table-bordered table-update' width='80%' id='test2'>" 
		. $district_names . $table_summary . $table_datas_summary . $total_facility_list. "<td>$total_facilities_in_county</td></tr>"
		.$total_targetted_facility_list."<td>$targetted_total</td>" . $percentage_coverage .
		"<td>$final_coverage_total %</td></tr>".$percentage_coverage_using."<td>$targetted_vs_using_hcmp %</td></tr></table>
		 </div>
		 </div>";
		
		if (isset($option)) :
			return $data_;
		else : echo $data_;
		endif;
		
				
		
	}
	
	//Downloads the excel file for user activities in a particular facility
	public function get_user_activities_excel($year = null, $month = null)
	{
		$year = (isset($year)&&($year>0))? $year:date("Y");
		$month = (isset($month)&&($month>0))? $month:date("m");
		$district_id = $this -> session -> userdata('district_id');
		$facility_code = $this -> session -> userdata('facility_id');
		
		$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$series_data_=array();
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		
		$facility_code_ = (isset($facility_code)&&($facility_code>0) )? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$title = (isset($facility_code) && isset($district_id))? "$district_name_ : $facility_name" :
		($district_id>0 && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
		
		$data = Log::get_user_activities_download($facility_code,$district_id,$county_id, $year, $month);
		
		foreach ($data as $data):
			$series_data_ = array_merge($series_data_ , array(array($data["time"],$data["fname"],$data["lname"],$data["facility_name"],$data["issued"],$data["ordered"],$data['decommissioned'],$data['redistribute'],$data['add_stock'],$data['no_activity'])));
		endforeach;
		
		$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' 
		=> "User Activities for $title ", 'file_name' => "User_Activities_for_$title");
		$row_data = array();
		$column_data = array("Time","First Name","Last Name","Facility Name","Issued Commodities?","Ordered Commodities?","Decommissioned Commodities?","Redistributed Commodities?","Added/Updated Commmodities?","Did Nothing?");
		$excel_data['column_data'] = $column_data;
		$row_data = array_merge($row_data,$series_data_);
		$excel_data['row_data'] = $row_data;;
		$this -> hcmp_functions -> create_excel($excel_data);
		
		
		
	}

	
	
	public function get_district_drill_down_detail($district_id, $date_of_activation) 
	{
		$district_data = "";
		$county_id = $this -> session -> userdata('county_id');
		$get_facility_data = facilities::get_facilities_reg_on_($district_id, urldecode($date_of_activation));
		
		foreach ($get_facility_data as $facility_data) :
			$facility_code = $facility_data -> facility_code;
			$facility_user_data = users::get_user_info($facility_code);
			$facility_name = $facility_data -> facility_name;
			$district_data .= '<span class="" width="100%"><b>' . $facility_name . '</b></span>
				<table class="row-fluid table table-hover table-bordered table-update" width="100%">
				<thead>
				<tr>
				<th>First Name</th><th>Last Name</th><th>Email </th><th>Phone No.</th>
				</tr>
				</thead>
				<tbody>';
			foreach ($facility_user_data as $user_data_) :
			$district_data .= "<tr><td>".$user_data_['fname']."</td><td>".$user_data_['lname']."</td><td>".$user_data_['email']."</td><td>".$user_data_['telephone']."</td>
			<tr>";
			endforeach;
			$district_data .= "</tbody></table>";
			endforeach;
	
		echo $district_data;

	}
	 //used for both the subcounty and county level program reports
	 public function program_reports()
	 {
	 	//echo $data['active_tab'];
//exit;
	 	$user_indicator = $district_id=$this -> session -> userdata('user_indicator');
	 	switch ($user_indicator) 
	 	{
			case district :
				$district_id = $this -> session -> userdata('district_id');
				$facilities = Facilities::get_district_facilities($district_id);
				$index = 0;
					foreach ($facilities as $ids)
					{
						$facility_id = $ids['facility_code'];
						$report_malaria = Malaria_Data::get_facility_report_details($facility_id);
						$report_RH = RH_Drugs_Data::get_facility_report_details($facility_id) ;
						
						if ((!empty($report_RH))&&(!empty($report_malaria)))
						{
							$report_RH_report[$index] = $report_RH;
							$report_malaria_report[$index] = $report_malaria;
							
						}else{
							
						}
						
						$index++;
					}
					
				$data['malaria'] = $report_malaria_report;
				$data['RH'] = $report_RH_report;
				$data['title'] = "Program Reports";
				$data['banner_text'] = "Program Reports";
			    $data['report_view'] = "subcounty/reports/program_reports_v";
				
			break;
			case county:
			 $county_id = $this -> session -> userdata('county_id');
				
			break;
		}
 		
 		
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view('shared_files/template/template', $data);
		
	 }
	
//generates the pdf for a particular report
	public function get_facility_report_pdf($report_id, $facility_code, $report_type) 
	{
		
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
			$facility_name = $myobj -> facility_name;
			// get the order form details here
			//create the pdf here
			$pdf_body = $this ->  create_program_report_pdf_template($report_id, $facility_code, $report_type);
			$file_name = $facility_name . '_facility_program_report_date_created_'. date('d-m-y');
			
			$pdf_data = array("pdf_title" => "Program Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'download', 'file_name' => $file_name);

			$this -> hcmp_functions -> create_pdf($pdf_data);
		redirect();
	}

	public function get_facility_bin_card_pdf($facility_code, $report_type,$commodity_id) 
	{
		  $county_id = $this -> session -> userdata('county_id');
		  $district_id = $this -> session -> userdata('district_id');
		  $param = implode('/', func_get_args());
		  $args = array();
		  $args = func_get_args();

		  $facility_code = $args[0];
		  $commodity_id = $args[1];
		  $from_ = array($args[2],$args[3],$args[4]);
		  $to_= array($args[5],$args[6],$args[7]);
		  $from=implode('/',$from_);
		  $to=implode('/',$to_);

		  $commodity_name = Commodities::get_commodity_name($commodity_id);
		  $county = Counties::get_county_name($county_id);
		  $district = Districts::get_district_name_($district_id);
		  $county_name = $county['county'];
		  $district_name=$district['district'];

		  $bin_card_data=Facility_issues::get_bin_card($facility_code,$commodity_id,$from,$to);

			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($facility_code);
			$facility_name = $myobj -> facility_name;

			$bin_card_data_count = count(Facility_issues::get_bin_card($facility_code,$commodity_id,$from,$to));
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $facility_code</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $district_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y') . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th><b>Commodity Name</b></th>
			<th><b>Date of Issue</b></th>
			<th><b>Reference No/S11 No</b></th>
			<th ><b>Commodity Unit Size</b></th>
			<th ><b>Batch No -Issued</b></th>
			<th ><b>Expiry Date</b></th>
			<th ><b>Opening Bal.</b></th>
			<th ><b>+ADJ</b></th>
			<th ><b>-ADJ</b></th>
			<th ><b>Receipts/Issues</b></th>
			<th ><b>Closing Bal.</b></th>
			<th ><b>Service Point</b></th>
			<th ><b>Issuing/Receiving Officer</b></th>
		</tr> 
		</thead>
		<tbody>";

		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $bin_card_data_count; $i++) 
		{
			$closing_balance =$bin_card_data[$i]['balance_as_of'] - $bin_card_data[$i]['qty_issued'];
			$mydrug_name = $commodity_name;
			$html_body .= "<tr>";
			$html_body .= "<td>" . $commodity_name[0]['commodity_name']. "</td>";
			$html_body .= "<td>". $bin_card_data[$i]['date_issued']."</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['s11_No'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['unit_size'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['batch_no'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['expiry_date'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['balance_as_of'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['adjustmentpve']. "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['adjustmentnve']. "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['qty_issued'] . "</td>";
			$html_body .= "<td>" . $closing_balance . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['service_point_name'] . "</td>";
			$html_body .= "<td>" . $bin_card_data[$i]['fname'] ." ".$bin_card_data[$i]['fname']. "</td>";
			$html_body .= "</tr>";
			
			
			
		}
	$html_body .= '</tbody></table></ol>';

			$pdf_body = $html_body;
			//end of bin card pdf

			$file_name = $facility_name . '_facility_program_report_date_created_'. date('d-m-y');
			
			$pdf_data = array("pdf_title" => "Program Report For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'download', 'file_name' => $file_name);

			$this -> hcmp_functions -> create_pdf($pdf_data);
		redirect();
	}//end of bin card pdf generation
	
	public function create_program_report_pdf_template($report_id, $facility_code, $report_type) 
	{
	if($report_type == "malaria")
		{
			//$report_time= strtotime($report_time);
			$from_malaria_data_table = Malaria_Data::get_facility_report($report_id, $facility_code);
			
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
			foreach ($from_malaria_data_table as $report_details) 
			{
				$mfl = $report_details['facility_id'];
				$commodity_code = $report_details['Kemsa_Code'];
				$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($commodity_code);
				$commodityname = $mydrug_name -> drug_name;
				
				$report_date = $report_details['Report_Date'];
						
				$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
				$sub_county_id = $myobj -> district;
				$facility_name = $myobj -> facility_name;
				// get the order form details here
	
				$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
				$sub_county_name = $myobj1 -> district;
				$county = $myobj1 -> county;
	
				$myobj2 = Doctrine::getTable('Counties') -> find($county);
				$county_name = $myobj2 -> county;
	
				$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
				$creator_email = $myobj_order -> email;
				$creator_name1 = $myobj_order -> fname;
				$creator_name2 = $myobj_order -> lname;
				$creator_telephone = $myobj_order -> telephone;
	
			}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th><b>KEMSA Code</b></th>
			<th><b>Commodity Name</b></th>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Received</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Losses Excluding Expiries</b></th>
			<th ><b>Adjustments</b></th>
			<th ><b>Physical Count</b></th>
			<th ><b>Expired Drugs</b></th>
			<th ><b>Days Out of Stock</b></th>
			<th ><b>Report Total</b></th>
		</tr> 
		</thead>
		<tbody>";

		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_malaria_data_table_count; $i++) 
		{
				
			$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($from_malaria_data_table[$i]['Kemsa_Code']);
			//$commodityname[$i] = $mydrug_name -> drug_name;
			$adjs = $from_malaria_data_table[$i]['Positive_Adjustments'] + $from_malaria_data_table[$i]['Negative_Adjustments'];
			$html_body .= "<tr>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Kemsa_Code'] . "</td>";
			$html_body .= "<td>" . $commodityname = $mydrug_name -> drug_name. "</td>";
			$html_body .= "<td>". $from_malaria_data_table[$i]['Beginning_Balance']."</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Quantity_Received'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Quantity_Dispensed'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Losses_Excluding_Expiries'] . "</td>";
			$html_body .= "<td>" . $adjs. "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Physical_Count'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Expired_Drugs'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Days_Out_Stock'] . "</td>";
			$html_body .= "<td>" . $from_malaria_data_table[$i]['Report_Total'] . "</td>";
			$html_body .= "</tr>";
			
			
			
		}
	$html_body .= '</tbody></table></ol>';
	
	}elseif($report_type == "RH")
	{
		$from_RH_data_table = RH_Drugs_Data::get_facility_report($report_id, $facility_code);
		$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
		
		foreach ($from_RH_data_table as $report_details) 
		{
			$mfl = $report_details['facility_id'];
			$report_date = $report_details['Report_Date'];
					
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
			$sub_county_id = $myobj -> district;
			$facility_name = $myobj -> facility_name;
			
			$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
			$sub_county_name = $myobj1 -> district;
			$county = $myobj1 -> county;

			$myobj2 = Doctrine::getTable('Counties') -> find($county);
			$county_name = $myobj2 -> county;

			$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
			$creator_email = $myobj_order -> email;
			$creator_name1 = $myobj_order -> fname;
			$creator_name2 = $myobj_order -> lname;
			$creator_telephone = $myobj_order -> telephone;
	
		}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Received This Month</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Losses</b></th>
			<th ><b>Adjustments</b></th>
			<th ><b>Ending Balance</b></th>
			<th ><b>Quantity Requested</b></th>
		</tr> 
		</thead>
		<tbody>";
		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_RH_data_table_count; $i++) 
		{
			$html_body .= "<tr>";
			$html_body .= "<td>". $from_RH_data_table[$i]['Beginning_Balance']."</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Received_This_Month'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Dispensed'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Losses'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Adjustments'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Ending_Balance'] . "</td>";
			$html_body .= "<td>" . $from_RH_data_table[$i]['Quantity_Requested'] . "</td>";
			$html_body .= "</tr>";
			
			
		}	
		
		$html_body .= '</tbody></table></ol>';
		
	}
	elseif($report_type == "TB")
	{
		$from_TB_data_table = tb_data::get_all_other($report_id);
		$from_TB_data_table_s2 = tb_data::get_all_other_2($report_id);
		$from_TB_data_table_count = count(tb_data::get_all_other($report_id));

		
		$tb_drug_names = tb_data::get_tb_drug_names();

		foreach ($from_TB_data_table as $report_details) 
		{
			$mfl = $report_details['facility_code'];
			$report_date = $report_details['report_date'];
					
			$myobj = Doctrine::getTable('Facilities') -> findOneByfacility_code($mfl);
			$sub_county_id = $myobj -> district;
			$facility_name = $myobj -> facility_name;
			
			$myobj1 = Doctrine::getTable('Districts') -> find($sub_county_id);
			$sub_county_name = $myobj1 -> district;
			$county = $myobj1 -> county;

			$myobj2 = Doctrine::getTable('Counties') -> find($county);
			$county_name = $myobj2 -> county;

			$myobj_order = Doctrine::getTable('users') -> find($report_details['user_id']);
			$creator_email = $myobj_order -> email;
			$creator_name1 = $myobj_order -> fname;
			$creator_name2 = $myobj_order -> lname;
			$creator_telephone = $myobj_order -> telephone;
	
		}
	
		//create the table for displaying the order details
		$html_body = "<table class='data-table' width=100%>
			<tr>
			<td>MFL No: $mfl</td> 
			<td>Health Facility Name:<br/> $facility_name</td>
			<td>Level:</td>
			<td>Dispensary</td>
			<td>Health Centre</td>
			</tr>
			<tr>
			<td>County: $county_name</td> 
			<td> District: $sub_county_name</td>
			<td >Reporting Period <br/>
			Start Date:  <br/>  End Date: " . date('d M, Y', strtotime($report_date)) . "
			</td>
			</tr>
			</table>";
		$html_body .= "
		<table class='data-table'>
		<thead>
		<tr>
			<th ><b>Drug Name</b></th>
			<th ><b>Pack</b></th>
			<th ><b>Beginning Date</b></th>
			<th ><b>Quantity Received This Month</b></th>
			<th ><b>Ending Date</b></th>
			<th ><b>Beginning Balance</b></th>
			<th ><b>Quantity Dispensed</b></th>
			<th ><b>Positive_Adjustments</b></th>
			<th ><b>Negative_Adjustments</b></th>
			<th ><b>Losses</b></th>
			<th ><b>Physical Count</b></th>
			<th ><b>Earliest Expiry</b></th>
			<th ><b>Quantity Requested</b></th>
			<th ><b>Report Date</b></th>
		</tr> 
		</thead>
		<tbody>";
		$html_body .= '<ol type="a">';
		for ($i = 0; $i < $from_TB_data_table_count; $i++) 
		{
			$html_body .= "<tr>";
			$html_body .= "<td>". $tb_drug_names[$i]['drug_name']."</td>";
			$html_body .= "<td>". $tb_drug_names[$i]['unit_size']."</td>";
			$html_body .= "<td>". $from_TB_data_table[$i]['beginning_date']."</td>";
			$html_body .= "<td>". $from_TB_data_table[$i]['currently_recieved']."</td>";
			$html_body .= "<td>". $from_TB_data_table[$i]['ending_date']."</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['beginning_balance'] . "</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['quantity_dispensed'] . "</td>";
			$html_body .= "<td>". $from_TB_data_table[$i]['positive_adjustment']."</td>";
			$html_body .= "<td>". $from_TB_data_table[$i]['negative_adjustment']."</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['losses'] . "</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['physical_count'] . "</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['earliest_expiry'] . "</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['quantity_needed'] . "</td>";
			$html_body .= "<td>" . $from_TB_data_table[$i]['report_date'] . "</td>";
			$html_body .= "</tr>";	
			
		}

		$html_body .= '</tbody></table></ol>';

	}elseif($report_type == "expiries"){

		$years = array();
		$month_names[] = array();

	$year1=	$years[0] = date('Y');
	$year2=	$years[1] =date('Y') +1;
	$year3=	$years[2] = date('Y') +2;

		$month_names[0] = 'January';
		$month_names[1] = 'February';
		$month_names[2] = 'March';
		$month_names[3] = 'April';
		$month_names[4] = 'May'; 
		$month_names[5] = 'June';
		$month_names[6] = 'July';
		$month_names[7] = 'August';
		$month_names[8] = 'September';
		$month_names[9] = 'October';
		$month_names[10] = 'November';
		$month_names[11] = 'December';


		$facility_code=isset($facility_code) ? $facility_code: $this -> session -> userdata('facility_id');
		$expiry_data = Facility_stocks::expiries_report($facility_code);
		$facility = $this -> session -> userdata('facility_id');
		$user_id = $this -> session -> userdata('user_id');
		$user_names = Users::get_user_names($user_id);
		$data['user_names'] = ($user_names[0]['fname']." ".$user_names[0]['lname']);
		
		$expiry_data_ = Facility_stocks::expiries_report($facility_code);
		$facility_info = tb_data::get_facility_name($facility_code);
		$facility_name = $facility_info['facility_name'];
		$facility_code_ = $facility_info['facility_code'];
		$facility_district = $facility_info['district'];
		$district_name_ = Districts::get_district_name_($facility_district);
		$district_name = $this -> session -> userdata('district');
		 
		$data['facility_code'] = $facility_info['facility_code']; 
		$district_region_name = $district_name_['district'];
		$data['facility_name'] = ($facility_info['facility_name']);
		$data['facility_type_'] = ($facility_info['owner']);
		// echo "<pre>";print_r($years);echo "</pre>";exit;
		//$data['expiry_data'] = Facility_stocks::expiries_report($facility_code);
		$facility_name=Facilities::get_facility_name_($facility_code)->toArray();
		$data['facility_name']=$facility_name[0]['facility_name'];

				$yrs_no = count($years);
		$months_no = count($month_names);
		for ($i=0; $i < $yrs_no; $i++) { 
			for ($j=0; $j < $months_no; $j++) { 
			$final_date[]=$month_names[$j]." ".$years[$i];
			}
			}
		$html_body= '
<body>

<div>

		<table width="100%"  class="data-table">
		<tbody>
		<tr>
		<th>Expiries Report</th>
			</tr>
			
			</table>
	</div>


<table width="100%"  class="data-table">

<tbody style = "display: table-row-group;vertical-align: middle;border-color: inherit;">
<form class ="form-control" id="tb_form" name="tb_form_">

	<div>
		<tr style = "display: table-row;vertical-align: inherit;border-color: inherit;">
		<thead>

			<th style = "border: 1px solid #ddd;" >Commodity</th>
			<th style = "border: 1px solid #ddd;" >Batch No</th>
			<th style = "border: 1px solid #ddd;" >Expiry Date</th>
			<th colspan="12" style="text-align: center;border: 1px solid #ddd;">'.$year1.'
			</th>
			<th colspan="12" style="text-align: center;border: 1px solid #ddd;">'.$year2.'
			</th>
			<th colspan="12" style="text-align: center;border: 1px solid #ddd;">'.$year3.'
		</th>

		</thead>
		</tr>

		<tr>
			<thead>

				<th style = "border: 1px solid #ddd;"></th>
				<th style = "border: 1px solid #ddd;"></th>
				<th style = "border: 1px solid #ddd;"></th>
				
				<th style = "border: 1px solid #ddd;">J</th>
				<th style = "border: 1px solid #ddd;">F</th>
				<th style = "border: 1px solid #ddd;">M</th>
				<th style = "border: 1px solid #ddd;">A</th>
				<th style = "border: 1px solid #ddd;">M</th>
				<th style = "border: 1px solid #ddd;">J</th>
				<th style = "border: 1px solid #ddd;">J</th>
				<th style = "border: 1px solid #ddd;" >A</th>
				<th style = "border: 1px solid #ddd;" >S</th>
				<th style = "border: 1px solid #ddd;" >O</th>
				<th style = "border: 1px solid #ddd;" >N</th>
				<th style = "border: 1px solid #ddd;" >D</th>
				
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >F</th>
				<th style = "border: 1px solid #ddd;" >M</th>
				<th style = "border: 1px solid #ddd;" >A</th>
				<th style = "border: 1px solid #ddd;" >M</th>
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >A</th>
				<th style = "border: 1px solid #ddd;" >S</th>
				<th style = "border: 1px solid #ddd;" >O</th>
				<th style = "border: 1px solid #ddd;" >N</th>
				<th style = "border: 1px solid #ddd;" >D</th>
				
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >F</th>
				<th style = "border: 1px solid #ddd;" >M</th>
				<th style = "border: 1px solid #ddd;" >A</th>
				<th style = "border: 1px solid #ddd;" >M</th>
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >J</th>
				<th style = "border: 1px solid #ddd;" >A</th>
				<th style = "border: 1px solid #ddd;" >S</th>
				<th style = "border: 1px solid #ddd;" >O</th>
				<th style = "border: 1px solid #ddd;" >N</th>
				<th style = "border: 1px solid #ddd;" >D</th>
			</thead>
		</tr>
		';

	foreach($expiry_data_ as $data):
	
	$checked = '<td><input type="checkbox"  checked = "checked" disabled ></td>';
	$unchecked = '<td><input type="checkbox" disabled ></td>';

	$jan14=$feb14=$march14=$april14=$may14=$june14=$july14=$aug14=$sept14=$oct14=$nov14=$dec14 = $unchecked;
	$jan15=$feb15=$march15=$april15=$may15=$june15=$july15=$aug15=$sept15=$oct15=$nov15=$dec15 = $unchecked;
	$jan16=$feb16=$march16=$april16=$may16=$june16=$july16=$aug16=$sept16=$oct16=$nov16=$dec16 = $unchecked;
	
$commodity = $data['commodity_name'];
$batch = $data['batch_no'];
$month = $data['expiry_month'];

// echo "<pre>";print_r($month);echo " </pre>";
 // echo "<pre>";print_r($final_date);echo " </pre>";exit;

	$html_body.= '
		<tr>
		<td>'.$commodity.'</td>
		<td>'.$batch.'</td>
		<td>
			'.$data['expiry_date'].'			
		</td>';
		switch ($month) {
			// 2014 SWITCH
			case NULL:
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
			
				break;

			case $final_date[0]:
			$jan14 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[1]:
			$feb14 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[2]:
			$march14 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
			
				break;

			case $final_date[3]:
			$april14 = $checked;

			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[4]:
			$may14 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[5]:
			$june14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[6]:
			$july14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[7]:
			$aug14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[8]:
			$sept14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[9]:
			$oct14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[10]:
			$nov14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[11]:
			$dec14 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

				// 2015
			case $final_date[12]:
			$jan15 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[13]:
			$feb15 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[14]:
			$march15 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
			
				break;

			case $final_date[15]:
			$april15 = $checked;

			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[16]:
			$may15 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[17]:
			$june15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[18]:
			$july15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[19]:
			$aug15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[20]:
			$sept15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[21]:
			$oct15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[22]:
			$nov15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[23]:
			$dec15 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			// 2016
			case $final_date[24]:
			$jan16 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[25]:
			$feb16 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[26]:
			$march16 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
			
				break;

			case $final_date[27]:
			$april16 = $checked;

			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[28]:
			$may16 = $checked;
			
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[29]:
			$june16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[30]:
			$july16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[31]:
			$aug16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[32]:
			$sept16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[33]:
			$oct16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[34]:
			$nov16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;

			case $final_date[35]:
			$dec16 = $checked;
			$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;
			
			default:
				$html_body.= $jan14.$feb14.$march14.$april14.$may14.$june14.$july14.$aug14.$sept14.$oct14.$nov14.$dec14;
			$html_body.= $jan15.$feb15.$march15.$april15.$may15.$june15.$july15.$aug15.$sept15.$oct15.$nov15.$dec15;
			$html_body.= $jan16.$feb16.$march16.$april16.$may16.$june16.$july16.$aug16.$sept16.$oct16.$nov16.$dec16;
				break;
		}

		endforeach;
		$html_body.= '</tr></div>
	</form>
</tbody>
</table>
';

		$pdf_data = array("pdf_title" => "Expiry Tracking Chart For $facility_name", 'pdf_html_body' => $pdf_body, 'pdf_view_option' => 'download', 'file_name' => $file_name);
		$html_body['title'] = $pdf_data;

	}

	// echo "<pre>";print_r($html_body);echo "</pre>";exit;
			
		return $html_body;
	}
	public function create_excel_facility_program_report($report_id,$facility_code, $report_type) 
	{
		if($report_type == "malaria")
		{
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			$from_malaria_data_table = Malaria_Data::get_facility_report($report_id, $facility_code);
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
						
			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility programm report template ', 'file_name' => $facility_details[0]['facility_name'].'facility programm report template ');
			$row_data = array();
			$column_data = array("Product Code","Item description(Name/form/strength)",
								"Beginning Balance", "Quantity Received",
								"Quantity Dispensed","Losses Excluding Expiries","Adjustments",
								"Physical Count", "Amount Expired","Days Out of Stock",
								"Report Total");
			$excel_data['column_data'] = $column_data;
			$from_malaria_data_table_count = count(Malaria_Data::get_facility_report($report_id, $facility_code));
			for($i=0;$i<$from_malaria_data_table_count;$i++):
			
			$adjs = $from_malaria_data_table[$i]['Positive_Adjustments'] + $from_malaria_data_table[$i]['Negative_Adjustments'];
			$mydrug_name = Doctrine::getTable('Malaria_drugs') -> findOneBykemsa_code($from_malaria_data_table[$i]['Kemsa_Code']);
			
			array_push($row_data, array($from_malaria_data_table[$i]["Kemsa_Code"], 
			$commodityname = $mydrug_name -> drug_name,
				$from_malaria_data_table[$i]["Beginning_Balance"], 
				$from_malaria_data_table[$i]["Quantity_Received"], 
				$from_malaria_data_table[$i]["Quantity_Dispensed"],
				$from_malaria_data_table[$i]["Losses_Excluding_Expiries"],
				$adjs,
				$from_malaria_data_table[$i]["Physical_Count"],
				$from_malaria_data_table[$i]["Expired_Drugs"],
				$from_malaria_data_table[$i]["Days_Out_Stock"],
				$from_malaria_data_table[$i]["Report_Total"])); 
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}elseif($report_type == "RH"){
			
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			
			$from_RH_data_table = RH_Drugs_Data::get_facility_report($report_id, $facility_code);
			$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
			
			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility program report ', 'file_name' => $facility_details[0]['facility_name'].'facility program report template');
			$row_data = array();
			$column_data = array("Beginning Balance",
								"Received This Month", 
								"Dispensed",
								"Losses",
								"Adjustments",
								"AEnding Balance",
								"Quantity Requested");
			$excel_data['column_data'] = $column_data;
			$from_RH_data_table_count = count(RH_Drugs_Data::get_facility_report($report_id, $facility_code));
			
			for($i=0;$i<$from_RH_data_table_count;$i++):
			
			array_push($row_data, array($from_RH_data_table[$i]["Beginning_Balance"], 
			$from_RH_data_table[$i]["Received_This_Month"], 
			$from_RH_data_table[$i]["Dispensed"], 
			$from_RH_data_table[$i]["Losses"],
			$from_RH_data_table[$i]["Adjustments"],
			$from_RH_data_table[$i]["Ending_Balance"],
			$from_RH_data_table[$i]["Quantity_Requested"])); //
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}


		elseif($report_type == "TB"){
			
			$facility_details = Facilities::get_facility_name_($facility_code) -> toArray();
			
			$from_TB_data_table = tb_data::get_all_other($report_id);
			$from_TB_data_table_2 = tb_data::get_all_other_2($report_id);
			$tb_drug_names = tb_data::get_tb_drug_names();
			
			$excel_data = array('doc_creator' => $facility_details[0]['facility_name'], 
			'doc_title' => 'facility program report ', 'file_name' => $facility_details[0]['facility_name'].'facility program report template');
			$row_data = array();
			$column_data = array("Drug Name",
								"Pack", 
								"Beginning Date",
								"Quantity Recieved This Month",
								"Ending Date",
								"Beginning Balance",
								"Quantity Dispensed",
								"Positive Adjustments",
								"Negative Adjustments",
								"Losses",
								"Physical Count",
								"Earliest Expiry",
								"Quantity Requested",
								"Report Date",
								);
			$excel_data['column_data'] = $column_data;
			$from_TB_data_table_count = count(tb_data::get_all_other($report_id));

			for($i=0;$i<$from_TB_data_table_count;$i++):
			
			array_push($row_data, array(
			$tb_drug_names[$i]["drug_name"], 
			$tb_drug_names[$i]['unit_size'], 
			$from_TB_data_table[$i]['beginning_date'],
			$from_TB_data_table[$i]['currently_recieved'], 
			$from_TB_data_table[$i]['ending_date'],
			$from_TB_data_table[$i]['beginning_balance'],
			$from_TB_data_table[$i]['quantity_dispensed'],
			$from_TB_data_table[$i]['positive_adjustment'],
			$from_TB_data_table[$i]['negative_adjustment'],
			$from_TB_data_table[$i]['losses'],
			$from_TB_data_table[$i]['physical_count'],
			$from_TB_data_table[$i]['earliest_expiry'],
			$from_TB_data_table[$i]['quantity_needed'],
			$from_TB_data_table[$i]["report_date"])); //
			endfor;
			$excel_data['row_data'] = $row_data;
	
			$this -> hcmp_functions -> create_excel($excel_data);
		}


		
	}
 /*
     |--------------------------------------------------------------------------|
     | COUNTY SUB-COUNTY dashboard                                              |
     |--------------------------------------------------------------------------|
     */
	 public function expiries_dashboard()
	 {
	 	$year = date("Y");	
		$county_id = $this -> session -> userdata('county_id');
		$district_id = $this -> session -> userdata('district_id');
		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	 	$option_new = 'units';
	 	$category_data = array();
		$graph_data = array();
		//$graph_type='';
		$title='';
		$series_data = $series_data_ = $series_data2 = array();		
		$temp_array = $temp_array_ =$temp_array2 = array();
		
		//Get the name of the facility if the facility code is set
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		
		//Get the name of the District
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? ": " . $district_data[0]['district'] . " Sub County" : null;
		
		//Get the name of the county
		$county_name = counties::get_county_name($county_id);
		 
	 	$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :
	 	(isset($district_id) && !isset($facility_code) ?  "$county_name[county] County$district_name_": "$county_name[county] County") ;
        
		
	 	$category_data = array_merge($category_data, $months);
		//for the expiries
		$commodity_array = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,$county_id, $year, null,$option ,"all");   
		$column_data_ = array("Expiries in $title $year", "stock expired in $option_new");		
		//for the potential expiries
		$commodity_array2 = Facility_stocks::get_county_cost_of_potential_expiries_new($facility_code,$district_id,$county_id, $year, null,$option ,"all");   
		
		foreach ($commodity_array as $data) :
			$temp_array = array_merge($temp_array, array($data["cal_month"] => (int)$data['total']));
			$series_data_ = array_merge($series_data_, array(array($data["cal_month"], (int)$data['total'])));
		endforeach;
		foreach ($commodity_array2 as $data2) :
			$temp_array2 = array_merge($temp_array2, array($data2["cal_month"] => (int)$data2['total_potential']));
			$series_data_2 = array_merge($series_data_2, array(array($data2["cal_month"], (int)$data2['total'])));
		endforeach;
		foreach ($months as $key => $data) :
			$val = (array_key_exists($data, $temp_array)) ? (int)$temp_array[$data] : (int)0;
			$val2 = (array_key_exists($data, $temp_array2)) ? (int)$temp_array2[$data] : (int)0;
			$series_data = array_merge($series_data, array($val));
			$series_data2 = array_merge($series_data2, array($val2));
	 	endforeach;
		
		$default_expiries = array();
		$default_expiries=array_merge($default_expiries,array("graph_id"=>'dem_graph_'));
		$graph_type='column';
	    $default_expiries=array_merge($default_expiries,array("graph_title"=>"Expiries in $title for $year"));
	    $default_expiries=array_merge($default_expiries,array("graph_type"=>$graph_type));
	    $default_expiries=array_merge($default_expiries,array("graph_yaxis_title"=>"$option_new"));
	    $default_expiries=array_merge($default_expiries,array("graph_categories"=>$category_data ));
		$default_expiries=array_merge($default_expiries,array("series_data"=>array()));
		
	
		$default_expiries['series_data']=array_merge($default_expiries['series_data'],array("Potential Expiries"=>$series_data2,"Actual Expiries"=>$series_data));
	  	    
		$data = array();
		
		$default_expiries_ = $this->hcmp_functions->create_high_chart_graph($default_expiries);
		$data['default_expiries'] = $default_expiries_;
		$data['district_data'] = districts::getDistrict($this -> session -> userdata('county_id'));
     		
		return $this -> load -> view("subcounty/ajax/county_expiry_filter_v", $data);	
	 }
	//For filtering for the expiries dashboard
	public function get_county_cost_of_expiries_dashboard($year = null, $district_id = null, $option = null, $facility_code = null,$report_type=null) 
	 {
	 	//get_county_cost_of_expiries_new/0/null/88/0/17401
	 	$year=($year=="NULL") ? null :$year;
	 	//$month=($month=="NULL") ? NULL :$month;
	 	$district_id=($district_id=="NULL") ? null :$district_id;
	 	$option=($option=="NULL") ? null :$option;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		$option=($option=="NULL" || $option=="null") ? null :$option;
		//set up the variables 
		$year = isset($year)? $year :date("Y");		
		$county_id = $this -> session -> userdata('county_id');
		$district_id_checker = $this -> session -> userdata('district_id');
		$county_name = counties::get_county_name($county_id);
		
		$category_data = array();
		$series_data =$series_data_=$series_data_2=$series_data_3 = $series_data2=array();		
		$temp_array =$temp_array2=$temp_array_ = array();
		$graph_data=array();
		$graph_type='';
		$title='';
		
        //months
		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');		
		//$month_ = isset($month) ?$months[(int) $month-1] : null ;
        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "units";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
       
	    //get the expiry for the entire year either for a facility sub-county or county     
     	$commodity_array = Facility_stocks::get_sub_county_cost_of_exipries($facility_code,$district_id,$county_id, $year, null,$option ,"all");   
		
		//for the potential expiries
		
		$commodity_array2 = Facility_stocks::get_county_cost_of_potential_expiries_new($facility_code,$district_id,$county_id, $year, null,$option ,"all");   
		foreach ($commodity_array as $data) :
			$temp_array = array_merge($temp_array, array($data["cal_month"] => (int)$data['total']));
			$series_data_ = array_merge($series_data_, array(array($data["district"], $data["facility_name"], $data["commodity_name"],$data["month"], (int)$data['total'])));
			$expiries_excel = array_merge($expiries_excel,array($data["cal_month"] => (int)$data['total']));
		endforeach;
		foreach ($commodity_array2 as $data2) :
			$temp_array2 = array_merge($temp_array2, array($data2["cal_month"] => (int)$data2['total_potential']));
			$series_data_2 = array_merge($series_data_2, array(array($data2["cal_month"], (int)$data2['total_potential'])));
			$series_data_3 = array_merge($series_data_3, array(array($data2["district"], $data2["facility_name"], $data2["commodity_name"], $data2["month_potential"], (int)$data2['total_potential'])));
			//$series_data_ = array_push($series_data_, array($data2["district"], $data2["facility_name"], $data2["commodity_name"], $data2["month_potential"], (int)$data2['total_potential']));
			//$series_data_ = array_merge($series_data_, $series_data_3); 
			$potential_excel = array_merge($potential_excel,array($data2["cal_month"] => (int)$data2['total_potential']));
		endforeach;
		foreach ($months as $key => $data) :
			$val = (array_key_exists($data, $temp_array)) ? (int)$temp_array[$data] : (int)0;
			$val2 = (array_key_exists($data, $temp_array2)) ? (int)$temp_array2[$data] : (int)0;
			$series_data = array_merge($series_data, array($val));
			$series_data2 = array_merge($series_data2, array($val2));
	 	endforeach;
	 	if($report_type=="csv_data"):
			
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => 
			"stock expired in $title in $year", 'file_name' => "Stock_expired$title in $year");
			$row_data = array();
			$column_data = array("Sub-county","Facility Name","Commodity Name"," Month expired(Actual expiries)","Actual expiries in $option_new");
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data,$series_data_);
			
			$excel_data['row_data'] = $row_data;
			
			$this -> hcmp_functions -> create_excel($excel_data);
		elseif($report_type=="table"):   
			
			$category_data = array(array("Sub-county","Facility Name","Commodity Name"," Month expired(Actual expiries)","Actual expiries in $option_new"));
			$graph_data=array_merge($graph_data,array("table_id"=>'graph_default'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data_));
			$data = array();
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);		
			$data['table_id'] ="graph_default";
			return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
			
		else:
		    $graph_type='column';
		    $graph_data=array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("graph_title"=>"Expiries in $title $month_ $year"));
		    $graph_data=array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data=array_merge($graph_data,array("graph_yaxis_title"=>"$option_new"));
		    $graph_data=array_merge($graph_data,array("graph_categories"=>$months ));
		    $graph_data=array_merge($graph_data,array("series_data"=>array()));
			$data = array();
			
			$graph_data['series_data']=array_merge($graph_data['series_data'],array("Potential Expiries"=>$series_data2,"Actual Expiries"=>$series_data));
	 		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
	
	
	}
	 public function get_county_cost_of_expiries_new($year = null, $month = null, $district_id = null, $option = null, $facility_code = null,$report_type=null) 
	 {
	 	
	 	//get_county_cost_of_expiries_new/0/null/88/0/17401
	 	$year=($year=="NULL") ? null :$year;
	 	$month=($month=="NULL") ? NULL :$month;
	 	$district_id=($district_id=="NULL") ? null :$district_id;
	 	$option=($option=="NULL") ? null :$option;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		$option=($option=="NULL" || $option=="null") ? null :$option;
		//set up the variables 
		$year = isset($year)? $year :date("Y");		
		$county_id = $this -> session -> userdata('county_id');
		$district_id_checker = $this -> session -> userdata('district_id');
		$county_name = counties::get_county_name($county_id);
		
		$category_data = array();
		$series_data =$series_data_ = array();		
		$temp_array =$temp_array_ = array();
		$graph_data=array();
		$graph_type='';
		$title='';
		
        //months
		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');		
		$month_ = isset($month) ?$months[(int) $month-1] : null ;
        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
        //get the expiry for the entire year either for a facility sub-county or county     
         
		if (!isset($month)) 
		{
			$category_data = array_merge($category_data, $months);
			$commodity_array = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,$county_id, $year, null,$option ,"all");   
			$column_data_ = array("stock expired in $title $month_ $year", "stock expired in $option_new");		
			foreach ($commodity_array as $data) :
				$temp_array = array_merge($temp_array, array($data["cal_month"] => (int)$data['total']));
				$series_data_ = array_merge($series_data_, array(array($data["cal_month"], (int)$data['total'])));
			endforeach;
			foreach ($months as $key => $data) :
			$val = (array_key_exists($data, $temp_array)) ? (int)$temp_array[$data] : (int)0;
			$series_data = array_merge($series_data, array($val));
			//$series_data_ = array_merge($series_data_, array($data,$val));
			
			endforeach;
			
			$graph_type='column';
		}
        //get the expiry for a specific month base on the set parameters
		if (isset($month) && $month>0) 
		{
			$commodity_array = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,$county_id, $year, $month,$option ,"all_");
			$column_data_ = array("stock expired in $title $month_ $year", "Month", "Stock expired in $option");

			foreach ($commodity_array as $data) :
				$series_data  = array_merge($series_data , array($data["name"] => (int) $data['total']));
				$category_data=array_merge($category_data, array($data["name"]));
				//array_push($series_data_, array($data["name"], (int) $data['total']));
			endforeach;
	        $graph_type='column';
			
			$commodity_array_for_excel = Facility_stocks::get_county_cost_of_exipries_new($facility_code,$district_id,$county_id, $year, null,$option ,null);
			
			foreach ($commodity_array_for_excel as $data_csv) :
				$series_data_ = array_merge($series_data_, array(array($data_csv["name"],$data_csv["month"], (int)$data_csv['total'])));
			endforeach;
			
			
		}
		
		if($report_type=="csv_data"):
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "stock expired in $commodity_name $title $month_ $year", 'file_name' => "Stock_expired_$commodity_name_$title_$month_$year");
			$row_data = array();
			$column_data = $column_data_;
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data,$series_data_);
			$excel_data['row_data'] = $row_data;
			
			$this -> hcmp_functions -> create_excel($excel_data);
		else:   
		    $graph_data=array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("graph_title"=>"stock expired in $title $month_ $year"));
		    $graph_data=array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data=array_merge($graph_data,array("graph_yaxis_title"=>"stock expired in $option_new"));
		    $graph_data=array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));
			$data = array();
		
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
	
	}

	public function load_stock_level_graph($district_id=NULL, $county_id=NULL, $facility_code=NULL,$commodity_id=null,$report_type,$tracer = null)
 	{
 		$tracer =(isset($tracer))? $tracer:null ;
      	$commodity_id = ($commodity_id=="NULL") ? null :$commodity_id;
	 	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$option = ($option=="NULL") ? null :$option;
		$category_id = ($category_id=="NULL") ? null :$category_id;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');

		//echo $district_id." Cty:".$county_id." Fcty:".$facility_code." Cmd_id:".$commodity_id." Report Type:".$report_type." TRacer:".$tracer;exit;

		$user = $this -> session -> userdata('user_indicator');
		//echo $user;exit;
		$county_id = $this -> session -> userdata('county_id');
		/*switch ($user) {
			case 'facility':
			case 'facility_admin':
			if( $facility_code!='NULL' || $district_id!="NULL"){
			$facility_code=($facility_code=='NULL')? $this -> session -> userdata('facility_id') : null;	
			}
			break;			
			case 'county':
			$county_id=($county_id=='NULL')?
			$this -> session -> userdata('county_id'): null;
			break;		
			case 'district':
			if($district_id!="NULL"){
			$district_id=($district_id=='NULL')? 
			$this -> session -> userdata('district_id'): null;
			}
			break;
			
			default:
				
			break;
		}*/

     	$final_graph_data = facility_stocks_temp::get_months_of_stock($district_id , $county_id , $facility_code ,$commodity_id,$report_type,$tracer);
        $graph_data = $series_data =array();
		$month = date('F Y');

			if (isset($commodity_id)){
				$commodity_name = Commodities::get_details($commodity_id)->toArray();
				$title .=' '.@$commodity_name[0]['commodity_name'];
			}
			 else{
				 $commodity_name = null;
			 }

			if (isset($county_id)){
				$county_name = counties::get_county_name($county_id);
				$title .=' '.$county_name['county']." County ";
			}
			 else{
				 $county_name = null;
			 }
			 
			 if (isset($district_id)){
			 	$district_name = districts::get_district_name_($district_id);
				 $title .=' '.$district_name['district']." Sub-County ";
			 }
			  else{
				 $district_name = null;
			 }
			  
			  if (isset($facility_code)){
				 $facility_name = Facilities::get_facility_name2($facility_code);
				 $title .=' '.$facility_name['facility_name'];
			}
			  else{
				 $facility_name = null;

			 }

		if($report_type=="table_data"):
			
       		$graph_data = array_merge($graph_data, array("graph_title" => "Months Of Stock For ".$title.""));
	
		    foreach ($final_graph_data as $data) :
			array_push($series_data , array($data["district"],$data["facility_name"],$data["commodity_name"],$data['total']));
		     endforeach;
        
			$category_data = array(array("Sub-county","Facility Name","Commodity Name","Month of Stock"));

	       	$graph_data=array_merge($graph_data,array("table_id"=>'graph_default'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
			$data=array();	
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);		
			$data['table_id'] ="graph_default";
			
			return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
			
		else:

    		$graph_type = 'bar';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Months Of Stock For ".$title.""));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Months of Stock"));
		    $graph_data = array_merge($graph_data, array("graph_categories" => array()));
			$graph_data = array_merge($graph_data, array("series_data" => array("Month of Stock" =>array())));	
			
			foreach($final_graph_data as $final_graph_data_):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'], array($final_graph_data_['commodity_name']));
				$graph_data['series_data']['Month of Stock'] = array_merge($graph_data['series_data']['Month of Stock'],array((int)$final_graph_data_['total']));	
			endforeach;
		    
		 	$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		 
			return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
			 
			 
	}

public function division_commodities_stock_level_graph($district_id=NULL, $county_id=NULL, $facility_code=NULL,$commodity_id=null,$division_id=NULL)

	{
			$county_id=$county_id=='NULL'? 
			($this -> session -> userdata('user_indicator') == 'county' ? 
			$this -> session -> userdata('county_id'): null) :$county_id;
			
			$district_id=$district_id=='NULL'? 
			($this -> session -> userdata('user_indicator') == 'district' ? 
			$this -> session -> userdata('district_id'): null) :$district_id;
			
			$facility_code=$facility_code=='NULL'? 
			($this -> session -> userdata('user_indicator') == 'facility' ? 
			$this -> session -> userdata('facility_code'): null) :$facility_code;
			
			$division_id=$division_id=='NULL'? null: $division_id;
			
			
         	$final_graph_data = facility_stocks_temp::get_division_commodities_stock($district_id , $county_id , $facility_code ,$division_id);
			$month = date('F Y');
			
			if (isset($division_id)){
				$division_name = commodity_division_details::get_all_divisions($division_id)->toArray();
				$title .=' '.@$division_name[0]['division_name'];
			}
			 else{
				 $division_name = null;
			 }
			if (isset($county_id)){
				$county_name = counties::get_county_name($county_id);
				$title .=' '.$county_name['county']." County ";
			}
			 else{
				 $county_name = null;
			 }
			 
			 if (isset($district_id)){
			 	$district_name = districts::get_district_name_($district_id);
				 $title .=' '.$district_name['district']." Sub-County ";
			 }
			  else{
				 $district_name = null;
			 }
			  
			  if (isset($facility_code)){
				 $facility_name = Facilities::get_facility_name2($facility_code);
				 $title .=' '.$facility_name['facility_name'];
			}
			  else{
				 $facility_name = null;
			 }

			 	 if((!isset($district_id)) && (!isset($facility_code)) && (!isset($division_id)) && (!isset($option)))
		{
			$county_id = $this -> session -> userdata('county_id');
			$county_name = counties::get_county_name($county_id);
			$title .=' '.$county_name['county']." County ";
			
		}



         	$graph_data = array();
       		$graph_data = array_merge($graph_data, array("graph_id" => 'default_graph_'));

			$graph_data = array_merge($graph_data, array("graph_title" => "Months Of Stock For ".$title.""));
			$graph_data = array_merge($graph_data, array("graph_type" => 'bar'));

			$graph_data = array_merge($graph_data, array("graph_yaxis_title" => 'Months of Stock'));

			$graph_data = array_merge($graph_data, array("graph_categories" => array()));
			$graph_data = array_merge($graph_data, array("series_data" => array("Stock" =>array())));	
	
	
			foreach($final_graph_data as $final_graph_data_):
			$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'], array($final_graph_data_['commodity_name']));
			$graph_data['series_data']['Stock'] = array_merge($graph_data['series_data']['Stock'],array((int)$final_graph_data_['total']));	
			endforeach;

		    $data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
				//echo $data['high_graph'];exit;
			return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
	}


   public function stock_level_dashboard()
   {
   
   		$tracer =(isset($tracer))? $tracer:null ;
      	$commodity_id = ($commodity_id=="NULL") ? null :$commodity_id;
	 	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$option = ($option=="NULL") ? null :$option;
		$category_id = ($category_id=="NULL") ? null :$category_id;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');

        //echo $district_id." Cty:".$county_id." Fcty:".$facility_code." Cmd_id:".$commodity_id." Report Type:".$report_type." TRacer:".$tracer;exit;
        
        

	 $district    = $this -> session -> userdata('district_id');
	 $county_id = $this -> session -> userdata('county_id');
	
	$user = $this -> session -> userdata('user_indicator');
	 $graph_data_default = $series_data = $series_data_ = array();
	 if ($user == "district") {
	 $district_name = districts::get_district_name($district) -> toArray();
	 $district_name_ = $district_name[0]['district'];
		 $title .=' '.$district_name_." Subcounty";
	 } 
	 elseif($user == "county"){
	 $county_name = counties::get_county_name($county_id);
	 $county_name_ = $county_name['county'];
		 $title .=' '.$county_name_." County ";
	 }
	 
	 $tracer=$report_type=1;
	 $final_graph_data = facility_stocks_temp::get_months_of_stock($district_id , $county_id , NULL ,NULL,$report_type,$tracer);
		//facility_stocks_temp::get_months_of_stock($district_id , $county_id , $facility_code ,$commodity_id,$report_type,$tracer)
	/*foreach($final_graph_data as $final_graph_data_):
			$graph_data_default['graph_categories'] = array_merge($graph_data_default['graph_categories'], array($final_graph_data_['commodity_name']));
			$graph_data['series_data']['Month of Stock'] = array_merge($graph_data['series_data']['Month of Stock'],array((int)$final_graph_data_['total']));	
	endforeach;*/
$graph_type = 'bar';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'default_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Months Of Stock For ".$title.""));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Months of Stock"));
		    $graph_data = array_merge($graph_data, array("graph_categories" => array()));
			$graph_data = array_merge($graph_data, array("series_data" => array("Month of Stock" =>array())));	
			
			foreach($final_graph_data as $final_graph_data_):
				$graph_data['graph_categories'] = array_merge($graph_data['graph_categories'], array($final_graph_data_['commodity_name']));
				$graph_data['series_data']['Month of Stock'] = array_merge($graph_data['series_data']['Month of Stock'],array((int)$final_graph_data_['total']));	
			endforeach;
			
		//$facility_code=(!$this -> session -> userdata('facility_id')) ? null: $this -> session -> userdata('facility_id');
		$district_id=(!$this -> session -> userdata('district_id')) ? null:$this -> session -> userdata('district_id');
		//$county_id=(!$this -> session -> userdata('county_id')) ? null:$this -> session -> userdata('county_id');
	    //compute stocked out items
	    $items_stocked_out_in_facility = count(facility_stocks::get_items_that_have_stock_out_in_facility($facility_code,$district_id,$county_id));
		//get order information from the db
		$facility_order_count_ = facility_orders::get_facility_order_summary_count($facility_code,$district_id,$county_id);
		$facility_order_count = array();
	     foreach($facility_order_count_ as $facility_order_count_)
	     {
	     	$facility_order_count[$facility_order_count_['status']]=$facility_order_count_['total'];
	     }
	    //get potential expiries infor here
	   // $potential_expiries = count(Facility_stocks::get_potential_expiry_summary($county_id,6,$district_id,$facility_code));
		$potential_expiries = count(Facility_stocks::getpotentialexpcount($county_id,$district_id));
	    //get actual Expiries infor here
	    $actual_expiries = count(Facility_stocks::getexpcount($county_id,$district_id));
		//get items they have been donated for
		$facility_donations = count(redistribution_data::get_redistribution_data($facility_code,$district_id,$county_id,date('Y')));
	    $identifier = $this -> session -> userdata('user_indicator');
	    //get the roll out status here
	    $facility_roll_out_status = Facilities::get_tragetted_rolled_out_facilities($facility_code,$district_id,$county_id,$identifier);
	    
		$data['county_dashboard_notifications'] = array(
		'items_stocked_out_in_facility'=>$items_stocked_out_in_facility,
		'facility_order_count'=>$facility_order_count,
		'potential_expiries'=>$potential_expiries,
		'actual_expiries'=>$actual_expiries,
		'facility_donations'=>$facility_donations,
	    'facility_roll_out_status'=>$facility_roll_out_status);	
		    
		 	$data['graph_data_default'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
			//return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
			//$graph_data_ = $this->hcmp_functions->create_high_chart_graph($graph_data_default);
			//$data['graph_data_default'] =	$graph_data_;			
     		$data['district_data'] = districts::getDistrict($this -> session -> userdata('county_id'));
     		$data['c_data'] = Commodities::get_all_2();
    		$data['tracer_items'] = Commodities::get_tracer_items();
	 		$data['division_commodities'] = commodity_division_details::get_all_divisions();
	 		$data['categories']= commodity_sub_category::get_all_pharm();
	 		$data['number_of_tracer_items'] = count(facility_stocks_temp::get_tracer_item_names());
	 		//$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);

     return $this -> load -> view("subcounty/ajax/county_stock_level_filter_v", $data);	

    }
   public function facility_stock_level_dashboard()
   {
		$county_id = $this -> session -> userdata('county_id');
		$view = 'shared_files/template/dashboard_template_v';
        $data['district_data'] = districts::getDistrict($county_id);
        $data['c_data'] = Commodities::get_all_2();
		$data['categories']=commodity_sub_category::get_all_pharm();
		$data['banner_text'] = "Stocking Levels";
		$data['title'] = "Stocking Levels";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$view = 'shared_files/template/template';
		$data['report_view'] = "subcounty/reports/county_stock_level_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_v";
		$data['active_panel']='stocking_levels';
 		$data['title'] = "Reports";
		$this -> load -> view($view, $data);

    }

	    public function tb_report(){
	    $data['title'] = "Facility Expiries";
		$data['banner_text'] = "Facility Tuberculosis & Leprosy Commodities Consumption Data Report & Request Form";
		$data['graph_data'] = $faciliy_expiry_data;
		$data['content_view'] = "subcounty/reports/tb_report";;
		$view = 'shared_files/template/template';
		$this -> load -> view($view, $data);
	    }

     	public function get_county_stock_level_new($commodity_id = null, $category_id = null, $district_id = null, $facility_code=null, $option = null,$report_type=null,$tracer = null) 
     	{	
     	//reset the values here
		$tracer =(isset($tracer))? $tracer:null ;
		$report_type =(isset($report_type))? $report_type:null ;
      	$commodity_id = ($commodity_id=="NULL") ? null :$commodity_id;
	 	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$option = ($option=="NULL") ? null :$option;
		$category_id = ($category_id=="NULL") ? null :$category_id;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');
		
     	// echo "<br><br><br><br>".$report_type;exit;
		
		if(($option=="mos")&&($report_type == '1')){
        //echo $district_id." Cty:".$county_id." Fcty:".$facility_code." Cmd_id:".$commodity_id." Report Type:".$report_type." TRacer:".$tracer;exit;
        return $this->load_stock_level_graph($district_id,$county_id,$facility_code,$commodity_id,$report_type,$tracer);
        }

        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "Ksh";
		$option_title = isset($option) ? $option : "Ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$commodity_name = (isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_ = @$commodity_name[0]['commodity_name'];
		$commodity_name = isset($category_name_)? " for ".$category_name_ : null;
		$title = isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;

		$commodity_array = facility_stocks::get_county_drug_stock_level_new($facility_code, $district_id, $county_id,
		$category_id, $commodity_id, $option_new, $report_type);

		$mos_array = facility_stocks_temp::get_months_of_stock($district_id , $county_id , $facility_code ,$commodity_id,$report_type,$tracer);
		
        foreach ($commodity_array as $data) :
			if($report_type=="table_data"):
				if($commodity_id>0):
					array_push($series_data , array($data['district'],$data["facility_name"],$data["facility_code"],(int)$data['total']));

				else:
					array_push($series_data , array($data["commodity_name"],$data["facility_name"],$data["name"],(int)$data['total']));
				endif;						
			else:
				$series_data  = array_merge($series_data , array((int)$data['total']));
				$series_data_  = array_merge($series_data_ , array(array($data['district'],$data["facility_name"],$data["facility_code"],$data["commodity_name"],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["commodity_name"]));
			endif;


		endforeach;
		
		if($report_type=="table_data"):
			if($commodity_id>0):
				$category_data = array(array("Sub-county","Facility Name","Mfl","TOTAL ".$option_new));
			else:
				array_push($category_data, array("Commodity Name","Facility Name","Sub-county Name","stocks worth in $option_title "));
				
			endif;	
	       	$graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
					
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
			$data['table_id'] ="dem_graph_";
			
			return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		
		elseif($report_type=="csv_data"):

			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "Stock level $commodity_name $title $month_ $year", 'file_name' => "Stock_level_$commodity_name_$title_$month_$year");
			$row_data = array();
			$column_data = array("Sub-county Name","Facility Name","MFL Code","Commodity Name","stocks worth in $option_title ");
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data,$series_data_); 
			//echo "<pre>";print_r($mos_array);echo "</pre>";exit;
			$excel_data['row_data'] = $row_data;
			$this -> hcmp_functions -> create_excel($excel_data);
		else:
			//echo "<pre>";print_r($category_data);echo "</pre>";exit;
    		$graph_type = 'column';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Stock Level $commodity_name for $title as at $month_ $year"));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Stock level in $option_title"));
		    $graph_data = array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data = array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));
			//echo $category_data;
		 $data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
			
	}
	//For the Sub County Comparison on the County and Sub County Interface
	public function get_county_comparison_graph($commodity_id = null, $category_id = null, $district_id = null, $facility_code=null, $option = null,$report_type=null,$tracer = null) 
 	{
     	$tracer =(isset($tracer))? $tracer:null ;
		$report_type =(isset($report_type))? $report_type:null ;
      	$commodity_id = ($commodity_id=="NULL") ? null :$commodity_id;
	 	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$option = ($option=="NULL") ? null :$option;
		$category_id = ($category_id=="NULL") ? null :$category_id;
	 	$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');
		
		/*if(($option=="mos")&&($report_type == '1'))
		{
	       return $this->load_stock_level_graph($district_id,$county_id,$facility_code,$commodity_id,$report_type,$tracer);
        }*/

        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "Ksh";
		$option_title = isset($option) ? $option : "Ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$commodity_name = (isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_ = @$commodity_name[0]['commodity_name'];
		$commodity_name = isset($category_name_)? " for ".$category_name_ : null;
		$title = isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;

		$commodity_array = facility_stocks::get_county_comparison_data($facility_code, $district_id, $county_id,$category_id, $commodity_id, $option_new, $report_type);
		
		//$mos_array = facility_stocks_temp::get_months_of_stock($district_id , $county_id , $facility_code ,$commodity_id,$report_type,$tracer);
		
        foreach ($commodity_array as $data) :
			if($report_type=="table_data"):
				if($commodity_id>0):
					array_push($series_data , array($data["name"],(int)$data['total_mos']));

				else:
					array_push($series_data , array($data["name"],(int)$data['total_mos']));
				endif;						
			else:
				$series_data  = array_merge($series_data , array((int)$data['total_mos']));
				//$series_data_  = array_merge($series_data_ , array(array($data['district'],$data["facility_name"],$data["facility_code"],$data["commodity_name"],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["name"]));
			endif;


		endforeach;
		
		if($report_type=="table_data"):
			if($commodity_id>0):
				$category_data = array(array("Sub-county","Facility Name","Mfl","TOTAL ".$option_new));
			else:
				array_push($category_data, array("Commodity Name","Facility Name","Sub-county Name","stocks worth in $option_title "));
				
			endif;	
	       	$graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
					
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
			$data['table_id'] ="dem_graph_";
			
			return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		
		/*elseif($report_type=="csv_data"):
			//echo "This is running";exit;
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "Stock level $commodity_name $title $month_ $year", 'file_name' => "Stock_level_$commodity_name_$title_$month_$year");
			$row_data = array();
			$column_data = array("Sub-county Name","Facility Name","MFL Code","Commodity Name","stocks worth in $option_title ");
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data,$series_data_); 
			//echo "<pre>";print_r($mos_array);echo "</pre>";exit;
			$excel_data['row_data'] = $row_data;
			$this -> hcmp_functions -> create_excel($excel_data);*/
		else:
    		$graph_type = 'bar';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Stock Level $commodity_name $title $month_ $year"));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Stock level in Month of Stock"));
		    $graph_data = array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data = array_merge($graph_data,array("series_data"=>array('Month of Stock'=>$series_data)));
			//echo "<pre>";print_r($graph_data);echo "</pre>";exit;
			//echo $category_data;
		 $data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
			
	}


public function get_division_commodities_data($district_id = null, $facility_code=null, $division_id = null, $option = null,$report_type=null) 
     	{
     	//reset the values here

      	$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$division_id = ($division_id=="NULL") ? null :$division_id;
	 	$option = ($option=="NULL") ? null :$option;
		$facility_code = ($facility_code=="NULL") ? null :$facility_code;
		$option = ($option=="NULL" || $option=="null") ? null :$option;	

     	//setting up the data
        if(($option=="mos")&&($report_type != "csv_data")&&($report_type != "table_data")){
        	return $this->division_commodities_stock_level_graph($district_id, $county_id, $facility_code,$commodity_id,$division_id);
        }
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		$category_data = $series_data = $series_data_ =  $graph_data = $data =array();
		$title='';	
		$year = date('Y');
		$month_ = date('M d');
        //check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name = $facility_code_[0]['facility_name'];
		$division_name = (isset($division_id))? Commodity_division_details::get_all_divisions($division_id)->toArray() : null;
		$division_name_ = @$division_name[0]['division_name'];
		$commodity_name = isset($division_name_)? " for ".$division_name_ : null;
		$title = isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	 	isset($district_id) && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
	
		$commodity_array = facility_stocks::get_county_drug_stock_level_new($facility_code, $district_id, $county_id,
		$category_id, $commodity_id, $option_new, $report_type,$division_id);
		
		// echo $facility_code." Dist: ". $district_id." Cty:". $county_id." Cat:".$category_id." Comm:". $commodity_id." Optn:". $option_new." Rpt typ:". $report_type." Div id:".$division_id;exit;
        foreach ($commodity_array as $data) :
         
			if($report_type=="table_data"):
				if($division_id>0):
					array_push($series_data , array($data['district'],$data["commodity_name"],$data["facility_name"],$data["facility_code"], (int)$data['total']));
				else:
					
					array_push($series_data , array($data["name"],(int) $data['total']));
				endif;						
			else:
				//$series_data_  = array_merge($series_data_ , array(array($data['district'],$data["facility_name"],$data["facility_code"],$data["commodity_name"],(int)$data['total'])));
				$series_data  = array_merge($series_data , array($data["name"] => (int)$data['total']));
				$series_data_  = array_merge($series_data_ , array(array($data["name"],$data["facility_name"],$data["commodity_name"],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["name"]));
				$category_data = array_unique($category_data);
			endif;

		endforeach;
		
		//echo "<pre>";print_r($category_data);echo "</pre>";
		//echo "<pre>"; print_r(array_unique($category_data));echo "</pre>";exit;
		/*foreach ($commodity_array as $data) :
		if ((isset($district_id) && isset($facility_code)) || ((isset($district_id)) && (!isset($facility_code)))):
				$category_data = array_merge($category_data, array($data["commodity_name"]));
				$series_data  = array_merge($series_data , array($data["commodity_name"]=> $data['total']));
		endif;	
			//echo $district_id.$facility_code;exit;
			//echo "<pre>";print_r($category_data);echo "</pre>";exit;
		endforeach;
		
		//echo "<pre>"; print_r($commodity_array);echo "</pre>";
		//echo "<pre>"; print_r($series_data);echo "</pre>";
		//echo "<pre>";print_r($category_data);echo "</pre>";exit;*/
		
		if($report_type=="table_data"):
			if($division_id>0):
				$category_data = array(array("Sub-county","Facility Name","Mfl","TOTAL ".$option_new));
			else:
				array_push($category_data, array("Stock level $commodity_name $title $month_ $year","stocks worth in $option_new"));
			endif;	
	       	$graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
		    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
		    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
					
			$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
			$data['table_id'] ="dem_graph_";
			return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
	
		elseif($report_type=="csv_data"):
			//echo "<pre>";print_r($series_data);echo "</pre>";exit;
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 'doc_title' => "Stock level $commodity_name $title $month_ $year", 'file_name' => "Stock_level_$commodity_name_$title_$month_$year");
			$row_data = array();
			$column_data = array("Stock level $commodity_name $title $month_ $year", "Commodity", "Facility", "stocks worth in $option_new");
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data, $series_data_); 
			
			$excel_data['row_data'] = $row_data;
			$this -> hcmp_functions -> create_excel($excel_data);
		else:
			
    		$graph_type = 'column';			
    		$graph_data = array_merge($graph_data,array("graph_id"=>'dem_graph_'));
		    $graph_data = array_merge($graph_data,array("graph_title"=>"Stock Level $commodity_name $title $month_ $year"));
		    $graph_data = array_merge($graph_data,array("graph_type"=>$graph_type));
		    $graph_data = array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Stock level in $option_new"));
		    $graph_data = array_merge($graph_data,array("graph_categories"=>$category_data ));
		    $graph_data = array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));

		 $data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
		endif;
		
	}


        public function consumption_data_dashboard($commodity_id = null, $district_id = null, $facility_code=null, $option = null,$from = null,$to = null, $graph_type=null,$tracer = null) {
       
	    //reset the values here
     	$commodity_id=($commodity_id=="NULL") ? null :$commodity_id;
		$district_id =(isset($district_id)&&($district_id))? $district_id:$this -> session -> userdata('district_id') ;
		$county_id =(isset($county_id)&&($county_id))? $county_id:$this -> session -> userdata('county_id') ;
	 	//$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		
		$from =(($from=="NULL")) ? strtotime(date('Y-m-01')) :strtotime(urldecode($from));	
		$to =(($to=="NULL")) ? strtotime(date('Y-m-d')) : strtotime(urldecode($to));
		
		$county_name = counties::get_county_name($county_id);
		

		$category_data = $series_data = $graph_data = $series_data_= $amc = array();

		//check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option = "packs";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name=$facility_code_[0]['facility_name'];
		$commodity_name=(isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_=@$commodity_name[0]['commodity_name'];
		$commodity_name=isset($category_name_)? " for ".$category_name_ : null;
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	    $district_id>0 && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
		$time= "between ".date('j M y', $from)." and ".date('j M y', $to);
		

		$consumption_data = Facility_stocks::get_county_consumption_level_new($facility_code,$district_id, $county_id,$category_id, $commodity_id, $option,$from, $to,$report_type,$tracer);
		
		
		foreach ($consumption_data as $data):
		    if($report_type=="table_data"):
				if($commodity_id>0):
					array_push($series_data , array($data['district'],$data["facility_name"],$data["facility_code"],$data['commodity'], (int)$data['total']));
				else:
					array_push($series_data , array($data["name"],$data['commodity'],(int)$data['total']));
				endif;	
			elseif($tracer==1):	
				$series_data  = array_merge($series_data , array((int)$data['total']));
				$series_data_ = array_merge($series_data_ , array(array($data["commodity"],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["commodity"]));			
			else:
				$series_data  = array_merge($series_data , array((int)$data['total']));
				$series_data_ = array_merge($series_data_ , array(array($data["name"],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["name"]));
			endif;
		
		endforeach;

		$default_consumption_graph_ = array();
		$graph_type='bar';			
        $default_consumption_graph_=array_merge($default_consumption_graph_,array("graph_id"=>'graph_content_'));
	    $default_consumption_graph_=array_merge($default_consumption_graph_,array("graph_title"=>"Consumption level $commodity_name $title $time"));
	    $default_consumption_graph_=array_merge($default_consumption_graph_,array("graph_type"=>$graph_type));
	    $default_consumption_graph_=array_merge($default_consumption_graph_,array("graph_yaxis_title"=>"$option_new"));
	    $default_consumption_graph_=array_merge($default_consumption_graph_,array("graph_categories"=>$category_data ));
	    $default_consumption_graph_=array_merge($default_consumption_graph_,array("series_data"=>array('Consumption'=>$series_data)));
		$data = array();

		$data = array();
		$def_cons=$this->hcmp_functions->create_high_chart_graph($default_consumption_graph_);
		$data['default_consumption_graph'] = $def_cons;
	 	$county_id = $this -> session -> userdata('county_id');
     	$data['district_data'] = districts::getDistrict($county_id);
     	$data['c_data'] = Commodities::get_all_2();
     	$data['tracer_items'] = Commodities::get_tracer_items();
	 	$data['categories']=commodity_sub_category::get_all_pharm();
		return $this -> load -> view("subcounty/ajax/county_consumption_data_filter_v", $data);
	}


	    public function consumption_stats_graph($commodity_id = null,$category_id = null, $district_id = null, $facility_code=null, $option = null,$from=null,$to=null,$report_type=null) {
	    //reset the values here
     	$commodity_id=($commodity_id=="NULL") ? null :$commodity_id;
		$district_id =(isset($district_id)&&($district_id))? $district_id:$this -> session -> userdata('district_id') ;
	 	//$district_id = ($district_id=="NULL") ? null :$district_id;
	 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
		$option=($option=="NULL" || $option=="null") ? null :$option;
		$from=($from=="NULL") ? strtotime(date('01-m-y')) :strtotime(urldecode($from));	
		$to=($to=="NULL") ? strtotime(date('d-m-y')) : strtotime(urldecode($to));
		$category_id=($category_id=="NULL") ? null :$category_id;		
		$county_id = $this -> session -> userdata('county_id');
		$county_name = counties::get_county_name($county_id);
		
		$category_data = $series_data = $graph_data= $series_data_=array();
		//check if the district is set
		$district_data = (isset($district_id) && ($district_id > 0)) ? districts::get_district_name($district_id) -> toArray() : null;
		$district_name_ = (isset($district_data)) ? " :" . $district_data[0]['district'] . " subcounty" : null;
		$option_new = isset($option) ? $option : "ksh";
		$facility_code_ = isset($facility_code) ? facilities::get_facility_name_($facility_code) -> toArray() : null;
		$facility_name=$facility_code_[0]['facility_name'];
		$commodity_name=(isset($commodity_id))? Commodities::get_details($commodity_id)->toArray() : null;
		$category_name_=@$commodity_name[0]['commodity_name'];
		$commodity_name=isset($category_name_)? " for ".$category_name_ : null;
		$title=isset($facility_code) && isset($district_id)? "$district_name_ : $facility_name" :( 
	    $district_id>0 && !isset($facility_code) ?  "$district_name_": "$county_name[county] county") ;
		$time= "between ".date('j M y', $from)." and ".date('j M y', $to);
		
		$consumption_data = Facility_stocks::get_county_consumption_level_new($facility_code,$district_id, $county_id,$category_id, $commodity_id, $option,$from, $to,$report_type);
		
		foreach ($consumption_data as $data):
		    if($report_type=="table_data"):
				if($commodity_id>0):
					array_push($series_data , array($data['district'],$data["facility_name"],$data["facility_code"],$data['commodity'], (int)$data['total']));
				else:
					array_push($series_data , array($data["name"],$data['commodity'],(int)$data['total']));
				endif;						
			else:
				$series_data  = array_merge($series_data , array((int)$data['total']));
				$series_data_ = array_merge($series_data_ , array(array($data["district"], $data["facility_name"], $data['commodity_name'],(int)$data['total'])));
				$category_data = array_merge($category_data, array($data["name"]));
			endif;
		//
		endforeach;
		
		if($report_type=="csv_data")://seth
			$excel_data = array('doc_creator' =>$this -> session -> userdata('full_name'), 
								'doc_title' => "Consumption level $commodity_name $title $time", 
								'file_name' => "consumption_level_$commodity_name_$title_$time");
			$row_data = array();
			$column_data = array("District", "Facility", "Commodity", "Consumption Level in $option_new");
			$excel_data['column_data'] = $column_data;
			$row_data = array_merge($row_data,$series_data_);
			//echo "<pre>";print_r($row_data);echo "</pre>";exit;
			$excel_data['row_data'] = $row_data;;
			$this -> hcmp_functions -> create_excel($excel_data);
			echo $commodity_name.$title.$time;exit;
		
		elseif($report_type=="table_data"):
			if($commodity_id>0):
				$category_data=array(array("Sub-county","Facility Name","Mfl","Commodity Name","TOTAL ".$option_new));
			else:
				array_push($category_data, array("Consumption level $commodity_name $title $time","Commodity Name","stocks worth in $option_new"));
			endif;	
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_";
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		else: 
        $graph_type='column';			
        $graph_data=array_merge($graph_data,array("graph_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("graph_title"=>"Consumption level $commodity_name $title $time"));
	    $graph_data=array_merge($graph_data,array("graph_type"=>$graph_type));
	    $graph_data=array_merge($graph_data,array("graph_yaxis_title"=>"Commodity Consumption level in $option_new"));
	    $graph_data=array_merge($graph_data,array("graph_categories"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("series_data"=>array('total'=>$series_data)));
		$data = array();
		$data['high_graph'] = $this->hcmp_functions->create_high_chart_graph($graph_data);
		return $this -> load -> view("shared_files/report_templates/high_charts_template_v", $data);
        endif;
	}
    public function notification_dashboard() 
    {
        $facility_code=(!$this -> session -> userdata('facility_id')) ? null: $this -> session -> userdata('facility_id');
		$district_id=(!$this -> session -> userdata('district_id')) ? null:$this -> session -> userdata('district_id');
		$county_id=(!$this -> session -> userdata('county_id')) ? null:$this -> session -> userdata('county_id');
	    //compute stocked out items
	    $items_stocked_out_in_facility = count(facility_stocks::get_items_that_have_stock_out_in_facility($facility_code,$district_id,$county_id));
		//get order information from the db
		$facility_order_count_ = facility_orders::get_facility_order_summary_count($facility_code,$district_id,$county_id);
		$facility_order_count = array();
	     foreach($facility_order_count_ as $facility_order_count_)
	     {
	     	$facility_order_count[$facility_order_count_['status']]=$facility_order_count_['total'];
	     }
	    //get potential expiries infor here
	    $potential_expiries = count(Facility_stocks::get_potential_expiry_summary($county_id,6,$district_id,$facility_code));
	    //get actual Expiries infor here
	    $actual_expiries = count(Facility_stocks::get_county_expiries($county_id,date('Y'),$district_id,$facility_code));
		//get items they have been donated for
		$facility_donations = count(redistribution_data::get_redistribution_data($facility_code,$district_id,$county_id,date('Y')));
	    $identifier = $this -> session -> userdata('user_indicator');
	    //get the roll out status here
	    $facility_roll_out_status = Facilities::get_tragetted_rolled_out_facilities($facility_code,$district_id,$county_id,$identifier);
	    
		$data['county_dashboard_notifications'] = array(
		'items_stocked_out_in_facility'=>$items_stocked_out_in_facility,
		'facility_order_count'=>$facility_order_count,
		'potential_expiries'=>$potential_expiries,
		'actual_expiries'=>$actual_expiries,
		'facility_donations'=>$facility_donations,
	    'facility_roll_out_status'=>$facility_roll_out_status);	
	    
		return $this -> load -> view("subcounty/ajax/county_notification_v", $data);
	}
     public function monitoring(){
        $facility_code=(!$this -> session -> userdata('facility_id')) ? null: $this -> session -> userdata('facility_id');
        $district_id=(!$this -> session -> userdata('district_id')) ? null:$this -> session -> userdata('district_id');
        $county_id=(!$this -> session -> userdata('county_id')) ? null:$this -> session -> userdata('county_id');
        $category_data=$series_data = $graph_data= $series_data_=array();
		$identifier = $this -> session -> userdata('user_indicator');
        $facility_data=Facilities::get_facilities_monitoring_data( $facility_code,$district_id,$county_id,$identifier);
        foreach($facility_data as $facility){
 $date=(strtotime($facility['last_seen']))? date('j M, Y',strtotime($facility['last_seen'])):"N/A" ;
          array_push($series_data,array(
          $date,
          $facility['days_last_seen'],
          date('j M, Y',strtotime($facility['last_issued'])) ,
          $facility['days_last_issued'],
          $facility['district'],
          $facility['facility_name'],
          $facility['facility_code'])) ; 
        }
        $category_data=array(array("date last seen","# of days","date last issued","# of days","sub county","Facility Name","Mfl"));
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
        $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
        $graph_data=array_merge($graph_data,array("table_body"=>$series_data));                
        $data['table'] = $this->hcmp_functions->create_data_table($graph_data);
        $data['table_id'] ="dem_graph_";
        return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
         
     }
    /*
	 |--------------------------------------------------------------------------
	 | COUNTY SUB-COUNTY reports
	 |--------------------------------------------------------------------------
	 */
		public function county_expiries() {
		$county_id=$this -> session -> userdata('county_id');
        $county_id=$this -> session -> userdata('county_id');
		//$year = date('Y');
        $data['year'] = date('Y');
		$data['title'] = "Expired Products";
		$data['banner_text'] = "Expired Products";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_expiries_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$data['active_panel']='expiries';	
		$this -> load -> view("shared_files/template/template", $data);
	}
		public function county_consumption() {
        $county_id=$this -> session -> userdata('county_id');
	    $data['district_data'] = districts::getDistrict($county_id);
	    $data['c_data'] = Commodities::get_all_2();
		$data['categories']=commodity_sub_category::get_all_pharm();
		$data['title'] = "Consumption";
		$data['banner_text'] = "Consumption";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/county_consumption_data_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$data['active_panel']='consumption';
		$this -> load -> view("shared_files/template/template", $data);
	}
//Function for commodity redistributions
		public function county_donation() 
		{
			$data['year'] = date('Y');
			$county_id=$this -> session -> userdata('county_id');
		    $data['district_data'] = districts::getDistrict($county_id);
			$data['title'] = $data['banner_text']="Donations";
			$data['content_view'] = "facility/facility_reports/reports_v";
			$data['report_view'] = "subcounty/reports/county_donation_filter_v";
		
		if($this->input->is_ajax_request()):
			return $this -> load -> view("subcounty/reports/county_donation_filter_v", $data);
		
		else:
			$data['report_view'] = "subcounty/reports/county_donation_filter_v";
			$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
			$data['active_panel']='donations';
			$this -> load -> view("shared_files/template/template", $data);
		endif;
		}
		public function stock_out(){
        $county_id=$this -> session -> userdata('county_id');
	    $data['district_data'] = districts::getDistrict($county_id);
		$data['title'] = $data['banner_text']="Stock Outs";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['active_panel']='stocking_levels';
		$data['report_view'] = "subcounty/reports/county_stock_out_facilities_filter_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_sub_county_v";
		$this -> load -> view("shared_files/template/template", $data);
         	
         }
	     public function actual_expiries_reports($county_id,$year){

		 $expiries_array=Facility_stocks::getexplist($county_id,$year);
		 $graph_data=$series_data=array();
		 $total_expiry=0;
		 foreach($expiries_array as $facility_expiry_data):
	     $district=$facility_expiry_data['district'];
		 $name=$facility_expiry_data['facility_name'];
		 $mfl=$facility_expiry_data['facility_code'];
		 $total=$facility_expiry_data['total'];	 
		 $total_expiry=$total_expiry+$total;
		 $year=date('Y');
		 $total=number_format($total, 2, '.', ',');
		 $district_id=$facility_expiry_data['district_id'];
		 $link=base_url("reports/expiries/$mfl");
	     array_push($series_data, array($district,$name,$mfl,$total,'<a href="'.$link.'">
         <button type="button" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-zoom-in"></span>View</button></a>'));
   	     endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	    array_push($series_data, array("","","Total for $year",$total_expiry,''));
	   
		$category_data=array(array("Sub-county","Facility Name","Mfl","TOTAL (ksh)",''));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_";
		// echo "<pre>";print_r($expiries_array);echo "</pre>";exit;
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}
	     public function potential_expiries_reports($county_id,$year){
	     $district_id = $this -> session -> userdata('district_id');
	     $dist_id = isset($district_id)? $district_id: null;
		 $expiries_array=Facility_stocks::get_potential_expiry_summary($county_id,$year,$dist_id);
		 $graph_data=$series_data=array();
		 $total_expiry=0;
		 foreach($expiries_array as $facility_expiry_data):
	     $district=$facility_expiry_data['district'];
		 $name=$facility_expiry_data['facility_name'];
		 $mfl=$facility_expiry_data['facility_code'];
		 $total=$facility_expiry_data['total'];	 
		 $total_expiry=$total_expiry+$total;
		 $total=number_format($total, 2, '.', ',');
		 $district_id=$facility_expiry_data['district_id'];
		 $link=base_url("reports/potential_exp_process_subcounty/$mfl");
	    array_push($series_data, array($district,$name,$mfl,$total,'<a href="'.$link.'">
         <button type="button" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-zoom-in"></span>View</button></a>'));
	    endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	    array_push($series_data, array("","","Total for the next $year months",$total_expiry,''));
	   
		$category_data=array(array("Sub-county","Facility Name","Mfl","TOTAL (ksh)",''));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
		$data['active_panel']="expiries";		
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}
        public function donation_reports($year=null,$district_id=null,$facility_code=null)
        {
        	//reset the values here
	     	$year=($year=="NULL") ? date('Y') :$year;
		 	$district_id=($district_id=="NULL") ? null :$district_id;
		 	$facility_code=($facility_code=="NULL") ? null :$facility_code;
	       	$county_id = $this -> session -> userdata('county_id');
			 $expiries_array=redistribution_data::get_redistribution_data($facility_code,$district_id,$county_id,$year);
			 $graph_data=$series_data=array();
			//echo "<pre>";print_r($expiries_array);echo "</pre>";
		 foreach($expiries_array as $facility_expiry_data):
	     $total_units=$facility_expiry_data['total_commodity_units'];
		 $sent_units=$facility_expiry_data['quantity_sent'];
		 $received_units=$facility_expiry_data['quantity_received'];
		 $total_sent=round(($sent_units/$total_units),1);	 
		 $total_received=round(($received_units/$total_units),1);    ///date_sent
         $date_received=strtotime($facility_expiry_data['date_received']) ? date('d M, Y', strtotime($facility_expiry_data['date_received'])) : "N/A";
         $status=$facility_expiry_data['status']==0 ? "<span class='label label-danger'>Pending</span>" : ( $facility_expiry_data['status']==1? "<span class='label label-success'>Received</span>" : null );
	    array_push($series_data, array($facility_expiry_data['source_facility_name']." :".$facility_expiry_data['source_facility_code'],
	    $facility_expiry_data['receiver_facility_name']." :".$facility_expiry_data['receiver_facility_code'],
		$facility_expiry_data['commodity_name'],$facility_expiry_data['source_district'],$facility_expiry_data['receiver_district'],$facility_expiry_data['unit_size'],$facility_expiry_data['batch_no'],
		date('d M, Y', strtotime($facility_expiry_data['expiry_date'])),$facility_expiry_data['manufacturer'],$total_sent,$sent_units,$total_received,$received_units,
		date('d M, Y', strtotime($facility_expiry_data['date_sent'])),$date_received,$status));
	    endforeach;
		$total_expiry=number_format($total_expiry, 2, '.', ',');
	   // array_push($series_data, array("","","Total for the next $year months",$total_expiry,''));
	   
		$category_data=array(array("From",'To',"Commodity Name","District From","District To",
		"Unit Size",'Batch No','Expiry Date','Manufacturer','Quantity Sent(units)','Quantity Sent(packs)',
		'Quantity Received (units)','Quantity Received (packs)','Date sent','Date Received','status'));
	
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
		}

         public function stock_out_reports($district_id=null,$facility_code=null){
         $district_id=($district_id=="NULL") ? null :$district_id;
	 	 $facility_code=($facility_code=="NULL") ? null :$facility_code;
         $county_id = $this -> session -> userdata('county_id');
		 $stock_out_array=Facility_stocks::get_items_that_have_stock_out_in_facility( $facility_code,$district_id,$county_id);
		 
		 $graph_data=$series_data=array();

		 foreach($stock_out_array as $facility_stock_data):
	     $day=date('j M, Y ',strtotime($facility_stock_data['last_day']));
         $ts1 = strtotime($facility_stock_data['last_day']); $ts2 = strtotime(date("Y/m/d")); $seconds_diff = $ts2 - $ts1;
         $days= floor($seconds_diff/3600/24);
	    array_push($series_data, array($facility_stock_data['district'],
	    $facility_stock_data['facility_name'],
		$facility_stock_data['facility_code'],$facility_stock_data['commodity_name'],$facility_stock_data['commodity_code'],$day,$days));
	    endforeach;

         $category_data=array(array("Sub County",'Facility Name',"MLF No.","Commodity Name","Commodity Code","Last day of usage",
"No. days out of stock"));

        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));
				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);
		$data['table_id'] ="dem_graph_1";
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);
   	
   }
     /*
	 |--------------------------------------------------------------------------
	 | COUNTY SUB-COUNTY Facility Mapping
	 |--------------------------------------------------------------------------
	 */
     public function mapping(){
     	$identifier = $this -> session -> userdata('user_indicator');
		$county_id=$district_id=null;
		if($identifier=='district'){
		$district_id=$this -> session -> userdata('district_id');	
		}else{
		$county_id=$this -> session -> userdata('county_id');	
		}     	
	    $data['district_data'] = districts::getDistrict($county_id,$district_id);
		$data['title'] = $data['banner_text']="Facility Mapping";
		$data['content_view'] = "facility/facility_reports/reports_v";
		$data['report_view'] = "subcounty/reports/facility_mapping_v";
		$data['sidebar'] = "shared_files/report_templates/side_bar_facility_mapping_v";
		$this -> load -> view("shared_files/template/template", $data);
     	
     }
	 
	 	public function get_district_facility_mapping_($district_id) {
		$facility_data = facilities::getFacilities($district_id);
		$dpp_details = Users::get_dpp_details($district_id) -> toArray();
		$district_name = districts::getDistrict(null,$district_id)-> toArray();;
		
        $table_body = "";
		$dpp_fname = '';
		$dpp_lname = '';
		$dpp_phone = '';
		$dpp_email = '';
		$indicator = "SubCounty";
		$no_of_facility_users = 0;
		$no_of_facility_users_online = 0;
		$no_of_facilities = 0;
		$no_of_facilities_using = 0;
		$no_of_facilities_using_targetted = 0;
        $series_data= $graph_data=array();
		if (count($dpp_details) > 0) {

			$dpp_fname = $dpp_details[0]['fname'];
			$dpp_lname = $dpp_details[0]['lname'];
			$dpp_phone = $dpp_details[0]['telephone'];
			$dpp_email = $dpp_details[0]['email'];

		}
		
		foreach ($facility_data as $facility_detail) {
			$facility_code = $facility_detail -> facility_code;
			$facility_extra_data = facilities::get_facility_status_no_users_status($facility_code);
			$no_of_facility_users = $no_of_facility_users + $facility_extra_data[0]['number_of_users'];
			$no_of_facility_users_online = $no_of_facility_users_online + $facility_extra_data[0]['number_of_users_online'];
			$no_of_facilities = $no_of_facilities + 1;
	
			$using=$facility_detail->using_hcmp;
            $status_radio=$facility_detail->targetted==1 ? 'checked="true"'  : null; 
			($using== 1) ? $no_of_facilities_using = $no_of_facilities_using + 1 : 
			$status = null;
			$temp = $facility_extra_data[0]['status'];
			$status_using=$using== 1? 'checked="true"'  : null;  
			$a_date = strtotime($facility_detail->date_of_activation) ? date('d M, Y', strtotime($facility_detail->date_of_activation)) : "N/A";
			($using== 1) ? $status = "<span class='label label-success'>Active</span>" : $status = "<span class='label label-warning'>Inactive</span>";
            ($facility_detail->targetted==1) ? $no_of_facilities_using_targetted=$no_of_facilities_using_targetted+1 : null;
	   	array_push($series_data, array($district_name[0]['district'],
	    $facility_detail->facility_name,
		$facility_code,$facility_detail->owner,$status,
		"<input id='$facility_detail->id' type='checkbox' name='targetted' class='checkbox'  $status_radio/>",
		"<input id='$facility_detail->id' name='using_hcmp' type='checkbox' class='checkbox'  $status_using/>",
		$a_date,
		$facility_extra_data[0]['number_of_users']
		));
		}

		$stats_data = '
		<table style="float:left">
		<tr>
		<td><label style=" font-weight: ">' . $district_name[0]['district'] . ' ' . $indicator . ' Pharmacist :</label></td>
		<td><a class="badge">' . $dpp_fname . ' ' . $dpp_lname . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Phone No.</label></td>
		<td><a class="badge">' . $dpp_phone . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Email Address</label></td>
		<td><a class="badge">' . $dpp_email . '</a></td>
		</tr>
		</table>
		<table style="float:left">
		<tr>
		<td><label style=" font-weight: ">Total No of Facilities</label></td>
		<td><a class="badge" >' . $no_of_facilities . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Facilities  Targeted</label></td>
		<td>	<a class="badge">' . $no_of_facilities_using_targetted. '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Facilities Using HCMP </label></td>
		<td>	<a class="badge">' . $no_of_facilities_using . '</a></td>
		</tr>
		<tr>
		<td><label style="font-weight: ">Total No of Users</label></td>
		<td><a class="badge" >' . $no_of_facility_users . '</a></td>
		</tr>
		</table>
        </br><p>';

        $category_data=array(array("Sub County",'Facility Name',"MLF No","Owner", "Facility Status","Targeted For Roll Out","Using HCMP","Date Activated", "No. Facility Users"));
        $graph_data=array_merge($graph_data,array("table_id"=>'dem_graph_1'));
	    $graph_data=array_merge($graph_data,array("table_header"=>$category_data ));
	    $graph_data=array_merge($graph_data,array("table_body"=>$series_data));				
		$data['table'] = $this->hcmp_functions->create_data_table($graph_data);		
		$data['table_id'] ="dem_graph_1";
		$data['stats_data']=$stats_data;
		return  $this -> load -> view("shared_files/report_templates/data_table_template_v", $data);

	}
       public function set_tragget_facility($facility_id,$status,$type){
       	//security check	  
       if($this->input->is_ajax_request()):
	   $date_ = date("Y-m-d");
	   $set=($type=='targetted') ? "`targetted` =$status" : "`using_hcmp` =$status, date_of_activation = '$date_'";
       $inserttransaction = Doctrine_Manager::getInstance()->getCurrentConnection();
	   $inserttransaction->execute("UPDATE `Facilities` SET $set
                                          WHERE `id`=$facility_id"); 
										  
	   echo "success";
       endif;
       }

public function list_facilities(){
	
		$county_id = $this -> session -> userdata('county_id');
		$data['content_view'] = "subcounty/reports/facility_list_v";
		$data['get_facility_data'] = facilities::get_facilities_online_per_district($county_id);
		$this -> load -> view("shared_files/template/template", $data);
       
       }
	 
  
}
?>
