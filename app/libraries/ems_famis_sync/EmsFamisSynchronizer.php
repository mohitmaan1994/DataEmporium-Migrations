<?php


namespace App\libraries\ems_famis_sync;

use App\libraries\ems_famis_sync\models\EmsAccounts;
use App\libraries\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmsFamisSynchronizer
{
    private $emsAccounts;
    protected $_settings;

    /**
     * EmsFamisSynchronizer constructor.
     * @param EmsAccounts $emsAccounts
     */

    public function __construct(EmsAccounts $emsAccounts)
    {
       $this->emsAccounts = $emsAccounts;
       $this->_settings = config('emsfamis');

    }

    public function synchronize()
    {

        extract($this->_settings);

        //Fetch data from delimited flatfile
        $selling_accounts = array();
        $accounts_raw = file_get_contents('//phpprod2/Webs/doit-api.tamu.edu/tmp/ems_accounts.txt');
        $accounts_raw_arr = explode("\r\n", $accounts_raw);
        foreach($accounts_raw_arr as $account_raw)
        {
            list($account_num, $account_name) = explode("|", $account_raw);
            $selling_accounts[$account_num] = $account_name;
        }

        $this->_settings['selling_account_names'] = $selling_accounts;

        //Traverse data and insert into database if entry is not present
        $data=array();
        foreach ($selling_accounts as $x => $x_value){
            $ems = new EmsAccounts;
            if(!empty($selling_accounts)) {
                $data = [
                    'account_number' => $x,
                    'account_short_code' => $x_value,
                ];
                $ems::firstOrNew($data)->save();
                            }
        }
        //$this->emsAccounts->fill($data)->save();


        //Fetch sql readable start date and end date from settings table using settings model
        $famis_start_date = DB::table('settings')->where('key','famis_date_start')->value('value');
        $famis_end_date = DB::table('settings')->where('key','famis_date_end')->value('value');

        $famis_timestamp_start = strtotime($famis_start_date);
        $famis_timestamp_end = strtotime($famis_end_date);

        $start_date = date('Y-m-d', $famis_timestamp_start).' 00:00:00.000';
        $end_date = date('Y-m-d', $famis_timestamp_end).' 00:00:00.000';


        //Get all tax accounts
        $accounts = DB::connection('emsfamis')->table('tblAccount')->join('tblCalculation','tblAccount.ID','=','tblCalculation.AccountID')->select('tblAccount.*','tblCalculation.ID AS TaxID')->get();
        $tax_accounts_raw = json_decode(json_encode($accounts),true);

        $tax_accounts = array();

        foreach($tax_accounts_raw as $tax_account)
            $tax_accounts[$tax_account['TaxID']] = str_replace('-', '', $tax_account['Account']);


        // Build the list of transactions by type.
        $idt_transactions = array();
        $ar_transactions = array();

        $all_new_transactions = DB::connection('emsfamis')->table('tblTransaction')->select('ID','BillingReference')->where('Void','=',0)->whereBetween('Date',[$start_date,$end_date])->get();
        $all_transactions = json_decode(json_encode($all_new_transactions),true);

        foreach($all_transactions as $transaction)
        {
            $buying_account = str_pad(substr(str_replace('-', '', trim($transaction['BillingReference'])), 0, config('emsfamis.buying_account_field_len')), config('emsfamis.buying_account_field_len'), '0', STR_PAD_RIGHT);

            $transaction_data = array(
                'TransactionID'	=> $transaction['ID'],
                'BuyingAccount' => $buying_account,
            );

            if (substr($buying_account, 0, 2) == "02")
                $idt_transactions[] = $transaction_data;
            else
                $ar_transactions[] = $transaction_data;
        }

        /**
         * Process IDT Transactions
         */

        $idt_text = array();
        $idt_rows = array();

        $listitems_by_object_code=array();

        foreach($idt_transactions as $transaction_data){

       $transactions_idt = DB::connection('emsfamis')->table('tblTransaction')->select('*')->where('ID','=', array($transaction_data['TransactionID']))->get();
       $transactions = json_decode(json_encode($transactions_idt),true);

            foreach($transactions as $transaction){

                $idt_listitems_get = DB::connection('emsfamis')->table('tblTransactionDetail as td')
                    ->leftJoin('tblServiceOrder as so','so.ID','=','td.ServiceOrderID')
                    ->leftJoin('tblBooking as bk','bk.ID','=','so.BookingID')
                    ->leftJoin('tblServiceOrderDetail as sod','sod.ID','=','td.ServiceOrderDetailID')
                    ->leftJoin('tblRoom as rm','rm.ID','=','bk.RoomID')
                    ->leftJoin('tblResource as r','r.ID','=','sod.ResourceID')
                    ->leftJoin('tblAccount as Resource','Resource.ID','=','r.AccountID')
                    ->leftJoin('tblAccount as Room','Room.ID','=','rm.AccountID')
                    ->select(DB::raw('CONVERT(varchar(11), td.Amount) AS Amount'),'td.dsplText as Description','td.CalcID As TaxID',
                    'so.CategoryID','td.RecordType','bk.RoomID','Room.Account as room_account','rm.Description as room_description',
                    'Resource.Account as resource_account','r.ResourceDescription as resource_description','sod.ResourceID')
                    ->where('td.TransactionID','=',array($transaction_data['TransactionID']))
                    ->whereIn('td.RecordType',[400,410,995,998])
                    ->where('td.Amount','<>','0')->get();

                $idt_listitems = json_decode(json_encode($idt_listitems_get),true);


                foreach($idt_listitems as $idt_listitem)
                {
                    $debit_credit = (strchr($idt_listitem['Amount'], '-') !== FALSE) ? 'C' : 'D';
                    $amount = str_replace(array('-','.'), '', $idt_listitem['Amount']);

                    if ($idt_listitem['RecordType'] == 995)
                    {
                        $tax_id = $idt_listitem['TaxID'];
                        $selling_account = $tax_accounts[$tax_id];
                        $description = $idt_listitem['Description'];
                    }
                    else
                    {
                        $selling_account = ($idt_listitem['CategoryID'] == '2147483647') ? $idt_listitem['room_account'] : $idt_listitem['resource_account'];
                        $description = ($idt_listitem['CategoryID'] == '2147483647') ? $idt_listitem['room_description'] : $idt_listitem['resource_description'];
                    }

                    $revenue_code = substr($selling_account, -4);
                    $buying_object_codes = config('emsfamis.buying_object_codes');
                    $object_code = (isset($buying_object_codes[$revenue_code])) ? $buying_object_codes[$revenue_code] : '0000';

                    $idt_row = array(
                        'amount'				=> ($debit_credit == 'C') ? 0-intval($amount) : intval($amount),
                        'text' 					=> array(
                            'SellingAccount'		=> str_pad(substr(trim(str_replace('-', '', $selling_account)), 0, config('emsfamis.selling_account_field_len')), config('emsfamis.selling_account_field_len'), '0', STR_PAD_RIGHT),
                            'BuyingAccount'			=> $transaction_data['BuyingAccount'].$object_code,
                            'Description'			=> str_pad(substr(trim($description), 0, config('emsfamis.description_field_len')), config('emsfamis.description_field_len'), ' ', STR_PAD_RIGHT),
                            'Amount'				=> str_pad(substr(trim($amount), 0, config('emsfamis.amount_field_len')), config('emsfamis.amount_field_len'), '0', STR_PAD_LEFT),
                            'ReferenceNumber1'		=> str_repeat('0', 7),
                            'ReferenceNumber2'		=> $transaction['InvoiceNo'],
                            'DebitCreditIndicator'	=> $debit_credit,
                            'Date'					=> date('Ymd', strtotime($transaction['Date'])),
                            'ReferenceNumber3'		=> str_repeat('0', 7),
                            'ReferenceNumber4'		=> str_repeat('0', 7),
                            'PostedDate'			=> date('Ymd', strtotime($transaction['DateAdded'])),
                            'Filler'				=> str_repeat(' ', 5),
                        ),
                    );


                    $listitems_by_object_code[$transaction['InvoiceNo']][$selling_account][] = $idt_row;
                }

            }

        }

        foreach($listitems_by_object_code as $invoice_no => $items)
        {
            foreach($items as $selling_account => $listitems)
            {
                $object_code = substr($selling_account, -4);

                $object_code_sum = 0;
                foreach($listitems as $listitem)
                {
                    $object_code_sum += $listitem['amount'];
                }
                $revenue_code_descriptions = config('emsfamis.revenue_code_descriptions');
                if ($object_code_sum != 0)
                {
                    $amount = str_pad(substr(trim(str_replace(array('-'), '', $object_code_sum)), 0, config('emsfamis.amount_field_len')), config('emsfamis.amount_field_len'), '0', STR_PAD_LEFT);

                    $debit_credit = ($object_code_sum < 0) ? 'C' : 'D';

                    $description = $revenue_code_descriptions[$object_code];
                    $description = str_pad(trim(substr($description, 0, config('emsfamis.description_field_len'))), config('emsfamis.description_field_len'), ' ', STR_PAD_RIGHT);

                    $idt_row = array_slice($listitems, 0, 1);
                    $idt_row = $idt_row[0]['text'];

                    $idt_row['Description'] = $description;
                    $idt_row['Amount'] = $amount;
                    $idt_row['DebitCreditIndicator'] = $debit_credit;

                    $idt_rows[] = $idt_row;
                    $idt_text[] = implode('', $idt_row);
                }
            }
        }
      //  var_dump($idt_text);

        /**
         * Process A/R Transactions
         */

        $ar_text = array();
        $ar_rows = array();

        foreach($ar_transactions as $transaction_data){

            $transactions_ar = DB::connection('emsfamis')->table('tblTransaction')->select('*')->where('ID','=', array($transaction_data['TransactionID']))->get();
            $transactions = json_decode(json_encode($transactions_ar),true);

            foreach($transactions as $transaction){

                $ar_listitems_get = DB::connection('emsfamis')->table('tblTransactionDetail as td')
                    ->leftJoin('tblServiceOrder as so','so.ID','=','td.ServiceOrderID')
                    ->leftJoin('tblBooking as bk','bk.ID','=','so.BookingID')
                    ->leftJoin('tblServiceOrderDetail as sod','sod.ID','=','td.ServiceOrderDetailID')
                    ->leftJoin('tblRoom as rm','rm.ID','=','bk.RoomID')
                    ->leftJoin('tblResource as r','r.ID','=','sod.ResourceID')
                    ->leftJoin('tblAccount as Resource','Resource.ID','=','r.AccountID')
                    ->leftJoin('tblAccount as Room','Room.ID','=','rm.AccountID')
                    ->select(DB::raw('CONVERT(varchar(11), td.Amount) AS Amount'),'td.dsplText as Description','td.CalcID As TaxID',
                        'so.CategoryID','td.RecordType','bk.RoomID','Room.Account as room_account','rm.Description as room_description',
                        'Resource.Account as resource_account','r.ResourceDescription as resource_description','sod.ResourceID')
                    ->where('td.TransactionID','=',array($transaction_data['TransactionID']))
                    ->whereIn('td.RecordType',[400,410,995,998])
                    ->where('td.Amount','<>','0')->get();

                $ar_listitems = json_decode(json_encode($ar_listitems_get),true);

                if (count($ar_listitems) != 0) {
                    $header = array(
                        'RecordType' => 'H1',
                        'CustNumber' => str_pad($transaction_data['BuyingAccount'], config('emsfamis.cust_number_field_len'), '0', STR_PAD_LEFT),
                        'InvoiceNumber' => str_pad($transaction['InvoiceNo'], config('emsfamis.invoice_number_field_len'), '0', STR_PAD_LEFT),
                        'InvoiceDept' => '',
                        'BillDate' => date('Ymd', strtotime($transaction['Date'])),
                        'BillDueDate' => date('Ymd', strtotime($transaction['DueDate'])),
                        'BillPeriodBegin' => str_repeat(' ', 8),
                        'BillPeriodEnd' => str_repeat(' ', 8),
                        'CustPONumber' => str_repeat(' ', 10),
                        'CustAcctNumber' => str_repeat(' ', 15),
                        'CustProjNumber' => str_repeat(' ', 10),
                        'WorkOrderNumber' => str_repeat(' ', 10),
                        'InvoiceType' => 'DP',
                        'Filler' => str_repeat(' ', 14),
                    );

                    $listitems_by_object_code = array();

                    // Loop through items to determine the current deposit account.
                    $deposit_accounts = array('0441', '0442', '0443', '0435', '0602', '0620');
                    $deposit_account = NULL;

                    $dept_name = 'UCEN ';

                    foreach ($ar_listitems as &$ar_listitem_row) {
                        if ($ar_listitem_row['RecordType'] == 995) {
                            $tax_id = $ar_listitem_row['TaxID'];
                            $selling_account = $tax_accounts[$tax_id];
                        } else {
                            $selling_account = ($ar_listitem_row['CategoryID'] == 2147483647) ? $ar_listitem_row['room_account'] : $ar_listitem_row['resource_account'];
                            $selling_account = str_pad(substr(trim(str_replace('-', '', $selling_account)), 0, config('emsfamis.selling_account_field_len')), config('emsfamis.selling_account_field_len'), '0', STR_PAD_LEFT);
                        }

                        $ar_listitem_row['selling_account'] = $selling_account;

                        if (in_array(substr($selling_account, -4), $deposit_accounts)) {
                            $deposit_account = $selling_account;
                        }

                        // Set department name for this set of items.
                        $account_num = substr($selling_account, 0, -4);
                        if (isset($this->_settings['selling_account_names'][$account_num])) {
                            $ar_listitem_row['selling_account_name'] = str_pad($this->_settings['selling_account_names'][$account_num], 5, ' ', STR_PAD_RIGHT);

                            if ($selling_account != config('emsfamis.tax_account'))
                                $dept_name = $ar_listitem_row['selling_account_name'];
                        } else {
                            //11-26-2013
                            //If the resource account is not found in ems_accounts.txt, match on the parent account to get the department code
                            $parent_account = substr($account_num, 0, -5) . "00000";
                            if (isset($this->_settings['selling_account_names'][$parent_account])) {
                                $ar_listitem_row['selling_account_name'] = str_pad($this->_settings['selling_account_names'][$parent_account], 5, ' ', STR_PAD_RIGHT);

                                if ($selling_account != config('emsfamis.tax_account'))
                                    $dept_name = $ar_listitem_row['selling_account_name'];
                            }
                        }
                    }

                    $header['InvoiceDept'] = $dept_name;

                    // Finish item processing.
                    $current_row = 0;
                    foreach ($ar_listitems as &$ar_listitem) {
                        $current_row++;
                        $debit_credit = (strchr($ar_listitem['Amount'], '-') !== FALSE) ? 'C' : 'D';
                        $amount = str_replace(array('-', '.'), '', $ar_listitem['Amount']);
                        $amount = str_pad(substr(trim($amount), 0, config('emsfamis.amount_field_len')), config('emsfamis.amount_field_len'), '0', STR_PAD_LEFT);

                        $selling_account = $ar_listitem['selling_account'];

                        // Switch the active selling account to the deposit account if the item is a credit.
                        // usually happens for record type 998 which is a deposit record
                        if ($debit_credit == 'C' && !in_array(substr($selling_account, -4), $deposit_accounts) && $deposit_account) {
                            $selling_account = $deposit_account;
                            if ($ar_listitem['selling_account_name'] == "") {
                                $current_account_num = substr($selling_account, 0, -4);
                                $ar_listitem['selling_account_name'] = str_pad($this->_settings['selling_account_names'][$current_account_num], 5, ' ', STR_PAD_RIGHT);
                            }
                        }

                        $description = ($ar_listitem['CategoryID'] == 2147483647) ? $ar_listitem['room_description'] : $ar_listitem['resource_description'];
                        $description = str_pad(trim($description), config('emsfamis.description_field_len'), ' ', STR_PAD_RIGHT);

                        $revenue_code = substr($selling_account, -4);
                        $buying_object_codes = config('emsfamis.buying_object_codes');
                        $object_code = (isset($buying_object_codes[$revenue_code])) ? $buying_object_codes[$revenue_code] : '0000';

                        $ar_row = array(
                            'amount' => ($debit_credit == 'C') ? 0 - intval($amount) : intval($amount),
                            'text' => array(
                                'RecordType' => 'L1',
                                'CustNumber' => $header['CustNumber'],
                                'InvoiceNumber' => $header['InvoiceNumber'],
                                'InvoiceLineNumber' => str_pad($current_row, config('emsfamis.invoice_line_number_field_len'), '0', STR_PAD_LEFT),
                                'InvoiceDept' => $ar_listitem['selling_account_name'],
                                'SellingAccount' => $selling_account,
                                'Description' => $description,
                                'Amount' => $amount,
                                'DebitCreditIndicator' => $debit_credit,
                                'Reference2' => str_repeat('0', 7),
                                'DateOfSale' => date('Ymd', strtotime($transaction['Date'])),
                                'Filler' => str_repeat(' ', 12),
                            ),
                        );

                        if (intval($ar_row['text']['SellingAccount']) != 0) {
                            $listitems_by_object_code[$selling_account][] = $ar_row;
                        }
                    }

                    $results_by_code = array();
                    $total_all_items = 0;
                    foreach ($listitems_by_object_code as $selling_account => $ar_listitems) {
                        $object_code_sum = 0;
                        foreach ($ar_listitems as $ar_listitem) {
                            $object_code_sum += $ar_listitem['amount'];
                            $total_all_items += $ar_listitem['amount'];
                        }

                        if ($object_code_sum != 0) {
                            $results_by_code[$selling_account] = array(
                                'total' => $object_code_sum,
                                'items' => $ar_listitems,
                            );
                        }
                    }

                    if ($total_all_items != 0) {
                        // Only post this item at all if its sum total is non-zero.
                        $ar_rows[] = $header;
                        $ar_text[] = implode('', $header);

                        $current_row = 0;
                        $revenue_code_descriptions = config('emsfamis.revenue_code_descriptions');
                        foreach ($results_by_code as $selling_account => $code_info) {
                            $current_row++;
                            $ar_listitems = $code_info['items'];

                            $object_code = substr($selling_account, -4);
                            $object_code_sum = $code_info['total'];

                            $amount = str_pad(substr(trim(str_replace(array('-', '.'), '', $object_code_sum)), 0, config('emsfamis.amount_field_len')), config('emsfamis.amount_field_len'), '0', STR_PAD_LEFT);
                            $debit_credit = ($object_code_sum < 0) ? 'C' : 'D';

                            $description = $revenue_code_descriptions[$object_code];
                            $description = str_pad(trim(substr($description, 0, config('emsfamis.description_field_len'))), config('emsfamis.description_field_len'), ' ', STR_PAD_RIGHT);

                            $ar_row = array_slice($ar_listitems, 0, 1);
                            $ar_row = $ar_row[0]['text'];

                            $ar_row['Description'] = $description;
                            $ar_row['Amount'] = $amount;
                            $ar_row['DebitCreditIndicator'] = $debit_credit;
                            $ar_row['InvoiceLineNumber'] = str_pad($current_row, config('emsfamis.invoice_line_number_field_len'), '0', STR_PAD_LEFT);

                            $ar_rows[] = $ar_row;
                            $ar_text[] = implode('', $ar_row);
                        }
                    }
                }


            }
        }

        //var_dump($ar_text);



// Write the files to the local filesystem.

        $ar_filename = 'K702XX.ARP1.EMS.DT'.date('ymd', $famis_timestamp_end);
        $idt_filename = 'K702XX.IDTP1.EMS.DT'.date('ymd', $famis_timestamp_end);

        //$ar_filename = 'K702XX.ARP1.EMS.DT';
        //$idt_filename = 'K702XX.IDTP1.EMS.DT';


        $ar_file_path = 'C:/Developer Destination/Applications'.$ar_filename;
        file_put_contents($ar_file_path, implode("\r\n", $ar_text));

        $idt_file_path = 'C:/Developer Destination/Applications'.$idt_filename;
        file_put_contents($idt_file_path, implode("\r\n", $idt_text));




    }
    public function uploadfiles(){

Storage::disk('custom-ftp')->allDirectories();

    }
}
