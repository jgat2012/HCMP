<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
 /**
 * @author Kariuki
 */
class orders extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->helper(array('form','url'));
		$this->load->library(array('hcmp_functions','form_validation'));
	}
	public function index() {
		//$this -> load -> view("shared_files/login_pages/login_v");
	}
	public function facility_order(){
		$facility_code=$this -> session -> userdata('facility_id'); 
		$data['content_view'] = "facility/facility_orders/facility_order_from_kemsa_v";
	    $data['title'] = "Facility New Order";
		$data['banner_text'] =  "Facility New Order";
	    $data['drawing_rights'] =100;
		$data['order_details']=$data['facility_order'] = Facility_Transaction_Table::get_commodities_for_ordering($facility_code);
		$data['facility_commodity_list']=Commodities::get_facility_commodities($facility_code);
		$this -> load -> view('shared_files/template/template', $data);	
	}
	public function update_facility_order($order_id,$rejected=null,$option=null){
		$order_data=facility_orders::get_order_($order_id)->toArray();
		$data['content_view'] = "facility/facility_orders/update_facility_order_from_kemsa_v";
	    $data['title'] = "Facility Update Order";
		$data['banner_text'] =  "Facility Update Order";
	    $data['rejected']=($rejected=='rejected')? 1 : 0;
	    $data['option_']=($option=='readonly')? 'readonly_' : 0;
		$data['order_details'] =$order_data;
		$data['facility_order'] =facility_order_details::get_order_details($order_id);
		$data['facility_commodity_list']=Commodities::get_facility_commodities($order_data[0]['facility_code']);
		$this -> load -> view('shared_files/template/template', $data);	
	}
	public function facility_new_order(){
		//security check
if($this->input->post('commodity_id')):
	$this->load->database();
	$data_array=array();
	$commodity_id= $this->input->post('commodity_id');//order details table details 
	$quantity_ordered_pack=$this->input->post('quantity');
	$quantity_ordered_units=$this->input->post('actual_quantity');
	$price=$this->input->post('price');
	$o_balance=$this->input->post('open');
	$t_receipts=$this->input->post('receipts');
	$t_issues=$this->input->post('issues');
	$adjustpve=$this->input->post('adjustmentpve');
	$adjustnve=$this->input->post('adjustmentnve');
	$losses=$this->input->post('losses');
	$days=$this->input->post('days');
	$c_stock=$this->input->post('closing');
	$comment=$this->input->post('comment');
	$s_quantity=$this->input->post('suggested');
	$amc=$this->input->post('amc');
	$workload= $this->input->post('workload'); //order table details
	$bed_capacity=$this->input->post('bed_capacity');
	$drawing_rights=$this->input->post('drawing_rights');
	$order_total=$this->input->post('total_order_value');
	$order_no=$this->input->post('order_no');
	$facility_code=$this -> session -> userdata('facility_id'); 
	$user_id=$this->session->userdata('user_id');
    $order_date=date('y-m-d'); 
	$number_of_id=count($commodity_id);
	
	for($i=0;$i<$number_of_id;$i++){	
		if($i==0){
		$order_details=array("workload"=>$workload,
		'bed_capacity'=>$bed_capacity,
		'order_total'=>$order_total,
		'order_no'=>$order_no,
		'order_date'=>$order_date,
		'facility_code'=>$facility_code,
		'ordered_by'=>$user_id);	
		$this->db->insert('facility_orders',$order_details);
		$new_order_no=$this->db->insert_id();		
		}
		$temp_array=array("commodity_id"=>$commodity_id[$i],
		'quantity_ordered_pack'=>$quantity_ordered_pack[$i],
		'quantity_ordered_unit'=>$quantity_ordered_pack[$i],
		'quantity_recieved'=>0,
		'price'=>$price[$i],
		'o_balance'=>$o_balance[$i],
		't_receipts'=>$t_receipts[$i],
		't_issues'=>$t_issues[$i],
		'adjustpve'=>$adjustpve[$i],
		'adjustnve'=>$adjustnve[$i],
		'losses'=>$losses[$i],
		'days'=>$days[$i],
		'c_stock'=>$c_stock[$i],
		'comment'=>$comment[$i],
		's_quantity'=>$s_quantity[$i],
		'amc'=>$amc[0],
		'order_number_id'=>$new_order_no);//create the array to push to the db		
		array_push($data_array,$temp_array); 
	}// insert the data here 
	$this->db->insert_batch('facility_order_details', $data_array); 
	
	$myobj = Doctrine::getTable('Facilities')->findOneByfacility_code($facility_code);
    $facility_name= $myobj->facility_name;// get the order form details here 
    //create the pdf here
	$pdf_body=$this->create_order_pdf_template($new_order_no); $file_name=$facility_name.'_facility_order_no_'.$new_order_no."_date_created_".date('d-m-y');
	$pdf_data=array("pdf_title"=>"Order Report For $facility_name",
	'pdf_html_body'=>$pdf_body,'pdf_view_option'=>'save_file',
	'file_name'=>$file_name);
	
	$this->hcmp_functions->create_pdf($pdf_data);
	
	$this->session->set_flashdata('system_success_message', "Facility Order No $new_order_no has Been Saved"); 
    redirect('reports/order_listing');	
	
endif;
	
	}
	public function update_facility_new_order(){
		//security check
if($this->input->post('commodity_id')):
	$this->load->database();
	$data_array=array();
	$rejected=$this->input->post('rejected');
	$commodity_id=$this->input->post('commodity_id');//order details table details 
	$quantity_ordered_pack=$this->input->post('quantity');
	$order_id=$this->input->post('order_number');
	$facility_order_details_id=$this->input->post('facility_order_details_id');
	$quantity_ordered_unit=$this->input->post('actual_quantity');
	$price=$this->input->post('price');
	$o_balance=$this->input->post('open');
	$t_receipts=$this->input->post('receipts');
	$t_issues=$this->input->post('issues');
	$adjustpve=$this->input->post('adjustmentpve');
	$adjustnve=$this->input->post('adjustmentnve');
	$losses=$this->input->post('losses');
	$days=$this->input->post('days');
	$c_stock=$this->input->post('closing');
	$comment=$this->input->post('comment');
	$s_quantity=$this->input->post('suggested');
	$amc=$this->input->post('amc');
	$workload= $this->input->post('workload'); //order table details
	$bed_capacity=$this->input->post('bed_capacity');
	$drawing_rights=$this->input->post('drawing_rights');
	$order_total=$this->input->post('total_order_value');
	$order_no=$this->input->post('order_no');
	//$facility_code=$this -> session -> userdata('facility_id'); 
	//$user_id=$this->session->userdata('user_id');
    $order_date=date('y-m-d'); 
	$number_of_id=count($commodity_id);
	
	for($i=0;$i<$number_of_id;$i++){	
		
$orders = Doctrine_Manager::getInstance()->getCurrentConnection()
->execute("INSERT INTO facility_order_details (  `id`,
  `order_number_id`,
  `commodity_id`,
  `quantity_ordered_pack`,
  `quantity_ordered_unit`,
  `price`,
  `o_balance`,
  `t_receipts`,
  `t_issues`,
  `adjustpve`,
  `losses`,
  `days`,
  `comment`,
  `c_stock`,
  `amc`,
  `adjustnve`)
VALUES ($facility_order_details_id[$i],
$order_id,
$commodity_id[$i],
$quantity_ordered_pack[$i],
$quantity_ordered_unit[$i],
$price[$i],
$o_balance[$i],
$t_receipts[$i],
$t_issues[$i],
$adjustpve[$i],
$losses[$i],
$days[$i],
'$comment[$i]',
$c_stock[$i],
$amc[$i],
$adjustnve[$i]
)
ON DUPLICATE KEY UPDATE 
         `commodity_id`=$commodity_id[$i],
		`quantity_ordered_pack`=$quantity_ordered_pack[$i],
		`quantity_ordered_unit`=$quantity_ordered_pack[$i],
		`price`=$price[$i],
		`o_balance`=$o_balance[$i],
		`t_receipts`=$t_receipts[$i],
		`t_issues`=$t_issues[$i],
		`adjustpve`=$adjustpve[$i],
		`adjustnve`=$adjustnve[$i],
		`losses`=$losses[$i],
		`days`=$days[$i],
		`c_stock`=$c_stock[$i],
		`comment`='$comment[$i]',
		`amc`=$amc[$i],
		`order_number_id`=$order_id");

	}// insert the data here 
	
	$myobj = Doctrine::getTable('facility_orders')->find($order_id);
    $myobj->workload=$workload;
    $myobj->bed_capacity=$bed_capacity;
    $myobj->order_no=$order_no;
    $myobj->order_total=$order_total;
    ($rejected==1)?  $myobj->status=1: null ;
    $myobj->save();
    
	$this->session->set_flashdata('system_success_message', "Facility Order No $order_id has Been Updated"); 
    redirect('reports/order_listing');	
    endif;
	
	}
	
	public function delete_facility_order($order_id){
		$reset_facility_order_table = Doctrine_Manager::getInstance()->getCurrentConnection();
	    $reset_facility_order_table->execute("DELETE FROM `facility_order_details` WHERE order_number_id=$order_id; ");	

	    $reset_facility_order_table = Doctrine_Manager::getInstance()->getCurrentConnection();
	    $reset_facility_order_table->execute("DELETE FROM `facility_orders` WHERE  id=$order_id; ");
		
		$this->session->set_flashdata('system_success_message', "Order Number $order_id has been deleted");
		
		redirect('reports/order_listing');
	}
	
     public function get_facility_sorf($order_id=null,$facility_code=null){
     if (isset($order_id) && isset($facility_code)):
	$myobj = Doctrine::getTable('Facilities')->findOneByfacility_code($facility_code);
    $facility_name= $myobj->facility_name;// get the order form details here 
    //create the pdf here
	$pdf_body=$this->create_order_pdf_template($order_id); $file_name=$facility_name.'_facility_order_no_'.$new_order_no."_date_created_".date('d-m-y');
	$pdf_data=array("pdf_title"=>"Order Report For $facility_name",
	'pdf_html_body'=>$pdf_body,'pdf_view_option'=>'download',
	'file_name'=>$file_name);
	
	$this->hcmp_functions->create_pdf($pdf_data);
	 endif;
	 redirect();
     }
    public function create_order_pdf_template($order_no){
       $from_order_table=facility_orders::get_order_($order_no); //get the order data here 
	   $from_order_details_table = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAll("SELECT a.sub_category_name, 
	    b.commodity_name, b.commodity_code, b.unit_size, b.unit_cost, 
		c.quantity_ordered_pack,c.quantity_ordered_unit, c.price,  c.quantity_recieved,
		c.o_balance, c.t_receipts, c.t_issues, c.adjustnve, c.adjustpve,
        c.losses, c.days, c.comment, c.c_stock,c.s_quantity
        FROM  commodity_sub_category a, commodities b, facility_order_details c
        WHERE c.order_number_id =$order_no
        AND b.id = c.commodity_id
        AND a.id = b.commodity_sub_category_id
        ORDER BY a.id ASC , b.commodity_name ASC ");// get the order details here 
	    $from_order_details_table_count=count(from_order_details_table);
		foreach($from_order_table as $order){
		$o_no=$order->order_no;	
	    $o_bed_capacity=$order->bed_capacity;	
		$o_workload=$order->workload;	
		$o_date=$order->order_date;
		$a_date=$order->approval_date; 
		$a_date=strtotime($a_date) ? date('d M, Y',strtotime($a_date)) : "N/A";
		//checkdate ( (int) date('m', $unixtime) , (int) date('d', $unixtime) , date('y', $unixtime ) ) ? date('d M, Y',$unixtime) : "N/A";
		$o_total=$order->order_total;
		$d_rights=$order->drawing_rights;
		$bal=$d_rights-$o_total;
		$creator=$order->ordered_by;
		$approver=$order->approved_by;
	    $mfl=$order->facility_code;
		
		$myobj = Doctrine::getTable('Facilities')->findOneByfacility_code($mfl);
        $sub_county_id= $myobj->district;// get the order form details here 
        
        $myobj1 = Doctrine::getTable('Districts')->find($sub_county_id);
	    $sub_county_name=$myobj1->district;
		$county=$myobj1->county;
		
		$myobj2 = Doctrine::getTable('Counties')->find($county);
		$county_name=$myobj2->county;
			
		$myobj_order = Doctrine::getTable('users')->find($creator);
		$creator_email=$myobj_order->email;
		$creator_name1=$myobj_order->fname;
		$creator_name2=$myobj_order->lname;
		$creator_telephone=$myobj_order->telephone;
		
		$myobj_order_ = Doctrine::getTable('users')->find($approver);
		$approver_email=$myobj_order_->email;
		$approver_name1=$myobj_order_->fname;
		$approver_name2=$myobj_order_->lname;
		$approver_telephone=$myobj_order_->telephone;
		   
        }
        //create the table for displaying the order details
$html_body="<table class='data-table' width=100%>
<tr>
<td>MFL No: $mfl</td> 
<td>Health Facility Name:<br/> $facility_name</td>
<td>Total OPD Visits & Revisits: $o_workload </td>
<td>Level:</td>
<td>Dispensary</td>
<td>Health Centre</td>
</tr>
<tr>
<td>County: $county_name</td> 
<td> District: $sub_county_name</td>
<td>In-patient Bed Days :$o_bed_capacity </td>
<td>Order Date:<br/> ".date('d M, Y',strtotime ($o_date))." </td>
<td>Order no. $o_no</td>
<td >Reporting Period <br/>
Start Date:  <br/>  End Date: ".date('d M, Y',strtotime ($o_date))."
</td>
</tr>
</table>";        
$html_body .="
<table class='data-table'>
<thead><tr><th><b>KEMSA Code</b></th><th><b>Description</b></th><th><b>Order Unit Size</b>
</th><th><b>Order Unit Cost</b></th><th ><b>Opening Balance</b></th>
<th ><b>Total Receipts</b></th><th><b>Total issues</b></th><th><b>Adjustments(-ve)</b></th><th><b>Adjustments(+ve)</b></th>
<th><b>Losses</b></th><th><b>Closing Stock</b></th><th><b>No days out of stock</b></th>
<th><b>Order Quantity (Packs)</b></th><th><b>Order Quantity (Units)</b></th><th>
<b>Order cost(Ksh)</b></th><th><b>Comment</b></th></tr> </thead><tbody>";

	   $html_body .='<ol type="a">' ;
       for($i=0;$i<$from_order_details_table_count;$i++){
       		if ($i==0) {
				$html_body .='<tr style="background-color:#C6DEFF;"> <td colspan="16" >
				 <li> '.$from_order_details_table[$i]['sub_category_name'].' </li> </td></tr>'; 
			   }      	
else if( $from_order_details_table[$i]['sub_category_name']!=$from_order_details_table[$i-1]['sub_category_name']){
       	 	$html_body .='<tr style="background-color:#C6DEFF;"> <td  colspan="15"> 
       	 	<li> '.$from_order_details_table[$i]['sub_category_name'].' </li> </td></tr>';
       	 }
         $adjpve=$from_order_details_table[$i]['adjustpve'];
		 $adjnve=$from_order_details_table[$i]['adjustnve'];
		 $c_stock=$from_order_details_table[$i]['c_stock'];
		 $o_t=$from_order_details_table[$i]['quantity_ordered_pack'];
		 $o_q=$from_order_details_table[$i]['price'];
		 $o_bal=$from_order_details_table[$i]['o_balance'];
		 $t_re=$from_order_details_table[$i]['t_receipts'];
		 $t_issues=$from_order_details_table[$i]['t_issues'];
		 $losses=$from_order_details_table[$i]['losses'];
		 $adj=	$adjpve+$adjnve;	 
		 if($o_bal==0 && $t_re==0 && $t_issues>0){
		 	$adj=$t_issues;
		 }
		 $c_stock=$o_bal+$t_re+$adj-$losses-$t_issues;
		 
		 if($c_stock<0){
		 	$adj=$c_stock*-1;
		 }
		  $c_stock=$o_bal+$t_re+$adj-$losses-$t_issues;
		 $html_body .="<tr>";
		 $html_body .="<td>".$from_order_details_table[$i]['commodity_code']."</td>"; 	
		 $html_body .="<td>".$from_order_details_table[$i]['commodity_name']."</td>";
		  $html_body .="<td>".$from_order_details_table[$i]['unit_size']."</td>";
		  $ot=number_format($o_t*$o_q, 2, '.', ',');
		  $order_total=$order_total+($o_t*$o_q);
		  $html_body .="<td>$o_q</td>";
		  $html_body .="<td>". $o_bal."</td>"; 
		  $html_body .="<td>".$t_re."</td>";
		  $html_body .="<td>".$t_issues."</td>";
		  $html_body .="<td>".$adjnve."</td>";
		  $html_body .="<td>".$adjpve."</td>";
		  $html_body .="<td>".$losses."</td>";
		  $html_body .="<td>".$c_stock."</td>";
		  $html_body .="<td>".$from_order_details_table[$i]['days']."</td>"; 
		  $html_body .="<td>$o_t</td>";
		  $html_body .="<td>".$from_order_details_table[$i]['quantity_ordered_unit']."</td>"; 
		  $html_body .="<td>$ot</td>"; 
		  $html_body .="<td>".$from_order_details_table[$i]['comment']."</td></tr>"; }

          $bal=$d_rights-$order_total;
		  $html_body .='</tbody></table></ol>'; 
		  $html_body1 ='<table class="data-table" width="100%" style="background-color: 	#FFF380;">
		  <tr style="background-color: 	#FFFFFF;" > <td colspan="4" ><div style="width:100%">
		  <div style="float:right; width: 40%;"> Total Order Value:</div>
		  <div style="float:right; width: 40%;" >KSH '.number_format($order_total, 2, '.', ',').'</div> </div></td></tr>
		  <tr style="background-color: 	#FFFFFF;"  > 
		  <td colspan="4" ><div>
		  <div style="float: left" > Drawing Rights Available Balance:</div>
		  <div style="float: right" >KSH		'.number_format($bal, 2, '.', ',').'</div> </td></tr>
		  <tr><td>FACILITY TEL NO:</td><td colspan="3">FACILITY EMAIL:</td>
		  </tr>
		  <tr><td >Prepared by (Name/Designation) '.$creator_name1.' '.$creator_name2.'
		  <br/>
		  <br/>Email: '.$creator_email.'</td><td>Tel: '.$creator_telephone.'</td><td>Date: '.date('d M, Y',strtotime ($o_date)).'</td><td>Signature</td>
		  </tr>
		  <tr><td>Checked by (Name/DPF/DPHN)  '.$approver_name1.' '.$approver_name2.'
		  <br/>
		  <br/>Email: '.$approver_email.'</td><td>Tel: '.$approver_telephone.'</td><td>Date: '.$a_date.'</td><td>Signature</td>
		  </tr>
		  <tr><td>Authorised by (Name/DMoH) 
		  <br/>
		  <br/>Email:</td><td>Tel: </td><td>Date:</td><td>Signature</td>		   
		  </tr>
		  </table>';
		   
		   return $html_body.$html_body1;
    	
    }
	
	
}