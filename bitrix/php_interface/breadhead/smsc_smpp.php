<?php
/**
 * @package net
 * Class for receiving or sending sms through SMPP protocol.
 * @version 0.2
 * @author paladin
 * @since 04/25/2006
 * @see http://www.smpp.org/doc/public/index.html - SMPP 3.4 protocol specification
 */
class SMPP{
    var $dc=0;
    //SMPP bind parameters
    var $system_type="WWW";
    var $interface_version=0x34;
    var $addr_ton=0;
    var $addr_npi=0;
    var $address_range="";
    //ESME transmitter parameters
    var $sms_service_type="";
    var $sms_source_addr_ton=0;
    var $sms_source_addr_npi=0;
    var $sms_dest_addr_ton=1;
    var $sms_dest_addr_npi=1;
    var $sms_esm_class=0;
    var $sms_protocol_id=0;
    var $sms_priority_flag=0;
    var $sms_schedule_delivery_time="";
    var $sms_validity_period="";
    var $sms_registered_delivery_flag=0;
    var $sms_replace_if_present_flag=0;
    var $sms_data_coding=8;
    var $sms_sm_default_msg_id=0;
    var $smpp_optional_param = array(
        '0005' => 'dest_addr_subunit',
        '0006' => 'dest_network_type',
        '0007' => 'dest_bearer_type',
        '0008' => 'dest_telematics_id',
        '000D' => 'source_addr_subunit',
        '000E' => 'source_network_type',
        '000F' => 'source_bearer_type',
        '0010' => 'source_telematics_id',
        '0017' => 'qos_time_to_live',
        '0019' => 'payload_type',
        '001D' => 'additional_status_info_text',
        '001E' => 'receipted_message_id',
        '0030' => 'ms_msg_wait_facilities',
        '0201' => 'privacy_indicator',
        '0202' => 'source_subaddress',
        '0203' => 'dest_subaddress',
        '0204' => 'user_message_reference',
        '0205' => 'user_response_code',
        '020A' => 'source_port',
        '020B' => 'destination_port',
        '020C' => 'sar_msg_ref_num',
        '020D' => 'language_indicator',
        '020E' => 'sar_total_segments',
        '020F' => 'sar_segment_seqnum',
        '0210' => 'SC_interface_version',
        '0302' => 'callback_num_pres_ind',
        '0303' => 'callback_num_atag',
        '0304' => 'number_of_messages',
        '0381' => 'callback_num',
        '0420' => 'dpf_result',
        '0421' => 'set_dpf',
        '0422' => 'ms_availability_status',
        '0423' => 'network_error_code',
        '0424' => 'message_payload',
        '0425' => 'delivery_failure_reason',
        '0426' => 'more_messages_to_send',
        '0427' => 'message_state',
        '0501' => 'ussd_service_op',
        '1201' => 'display_time',
        '1203' => 'sms_signal',
        '1204' => 'ms_validity',
        '130C' => 'alert_on_message_delivery',
        '1380' => 'its_reply_type',
        '1383' => 'its_session_info');

    /**
     * Constructs the smpp class
     * @param $host - SMSC host name or host IP
     * @param $port - SMSC port
     */
    function SMPP($host, $port=5016){
        //internal parameters
        $this->deliver_sm_total_sgm = array(); // same as above
        $this->deliver_sm_payloads = array(); //might have many messages segements distributed on many users
        $this->attempts = 0;
        $this->message_sequence = rand(1,255);
        $this->start_enquire = 0;
        $this->sequence_number = 1;
        $this->debug=false;
        $this->pdu_queue=array();
        $this->host=$host;
        $this->port=$port;
        $this->state="closed";
        //open the socket
        $this->socket=fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if($this->socket)$this->state="open";
    }
    /**
     * Binds the receiver. One object can be bound only as receiver or only as trancmitter.
     * @param $login - ESME system_id
     * @param $port - ESME password
     * @return true when bind was successful
     */
    function bindReceiver($login, $pass){
        if($this->state!="open")return false;
        if($this->debug){
            echo "Binding receiver...\n\n";
        }
        $status=$this->_bind($login, $pass, 0x00000001);
        if($this->debug){
            echo "Binding status  : $status\n\n";
        }
        if($status===0)$this->state="bind_rx";
        return ($status===0);
    }
    /**
     * Binds the transmitter. One object can be bound only as receiver or only as trancmitter.
     * @param $login - ESME system_id
     * @param $port - ESME password
     * @return true when bind was successful
     */
    function bindTransmitter($login, $pass){
        if($this->state!="open")return false;
        if($this->debug){
            echo "Binding transmitter...\n\n";
        }
        $status=$this->_bind($login, $pass, 0x00000002);
        if($this->debug){
            echo "Binding status  : $status\n\n";
        }
        if($status===0)$this->state="bind_tx";
        return ($status===0);
    }

    /**
     * Binds the transceiver. One object can be bound only as transceiver.
     * @param $login - ESME system_id
     * @param $port - ESME password
     * @return true when bind was successful
     */
    function bindTransceiver($login, $pass){
        if($this->state!="open")return false;
        if($this->debug){
            echo "Binding transceiver...\n\n";
        }
        $status=$this->_bind($login, $pass, 0x00000009);
        if($this->debug){
            echo "Binding status  : $status\n\n";
        }
        if($status===0)$this->state="bind_tcx";
        return ($status===0);
    }
    /**
     * Closes the session on the SMSC server.
     */
    function close($keep_alive = false){
        if($this->state=="closed")return;
        if($this->debug){
            echo "Unbinding...\n\n";
        }
        $status=$this->sendCommand(0x00000006,"");
        if($this->debug){
            echo "Unbind status   : $status\n\n";
        }
        if(!$keep_alive)
            fclose($this->socket);
        $this->state="closed";
    }
    /**
     * Read one SMS from SMSC. Can be executed only after bindReceiver() call.
     * This method bloks. Method returns on socket timeout or enquire_link signal from SMSC.
     * @return sms associative array or false when reading failed or no more sms.
     */
    function readSMS(){
        if(($this->state!="bind_rx") && ($this->state!="bind_tcx"))return false;
        stream_set_timeout($this->socket, 10);
        $command_id=0x00000005;
        //check the queue
        for($i=0;$i<count($this->pdu_queue);$i++){
            $pdu=$this->pdu_queue[$i];
            if($pdu['id']==$command_id){
                //remove responce
                array_splice($this->pdu_queue, $i, 1);
                return $this->parseSMS($pdu);
            }
        }
        //read pdu
        do{
            if($this->debug){
                echo "read sms...\n\n";
            }
            $pdu=$this->readPDU();
            // check for enquire link command
            if($this->start_enquire) {
                if($pdu['id']==0x80000015){
                    if ($pdu['sn'] == ($this->sequence_number - 1)) {
                        sleep(6);
                        echo "sending enquire_link\n";
                        $this->sendPDU(0x00000015, "", $this->sequence_number++);
                        return false;
                    } else {
                        /** stupid smsc, sequence numbers are out of sync or the smsc
                        /* didn't send enquire link, boucning the connection.. this shit is no fun
                         */
                        echo "smsc sequence numbers out of sync, bounce the connection\n\n";
                        $this->close(); // close the socket and mark the conection closed
                        // open the socket again and mark the status open
                        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
                        if ($this->socket) {
                            $this->state="open";
                            $this->bindTransceiver("SEMAIL","pass");
                            $this->sequence_number = 1;
                            $this->sendPDU(0x00000015, "", $this->sequence_number++);
                        } else // problem opening the socket
                            echo "fsockopen at file:" . __FILE__ . " line:". __LINE__ . $errno . ' ' . $errstr;

                        return false;
                    }
                } else if($pdu['id']==0x00000015) { //check strictly for enquire_link
                    // same stupid thing, expecting enquire_link_resp, but not received
                    echo "smsc is not responding to my enquire_links, bounce the connection\n\n";
                    $this->close(); // close the socket and mark the conection closed
                    // open the socket again and mark the status open
                    $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
                    if ($this->socket) {
                        $this->state="open";
                        $this->bindTransceiver("SEMAIL","pass");
                        $this->sequence_number = 1;
                        $this->sendPDU(0x00000015, "", $this->sequence_number++);
                    } else // problem opening the socket
                        echo "fsockopen at file:" . __FILE__ . " line:". __LINE__ . $errno . ' ' . $errstr;

                    return false;
                } else { // do some check on how many times the pipe is empty
                    if ($this->attempts < 25)
                        echo 'reading sms, attempt #' . $this->attempts++ . "\n";
                    else {
                        echo "enough! 25 times and no incoming data, bounce the connection\n";
                        $this->attempts = 0;
                        $this->close(); // close the socket and mark the conection closed
                        // open the socket again and mark the status open
                        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
                        if ($this->socket) {
                            $this->state="open";
                            $this->bindTransceiver("SEMAIL","pass");
                            $this->sequence_number = 1;
                            $this->sendPDU(0x00000015, "", $this->sequence_number++);
                        } else // problem opening the socket
                            echo "fsockopen at file:" . __FILE__ . " line:". __LINE__ . $errno . ' ' . $errstr;

                        return false;
                    }
                }
            } else {
                if($pdu['id']==0x00000015){
                    echo "send enquire_link_resp\n\n";
                    $this->sendPDU(0x80000015, "", $pdu['sn']);
                    return false;
                }
            }
            array_push($this->pdu_queue, $pdu);
        }while($pdu && $pdu['id']!=$command_id);
        if($pdu){
            array_pop($this->pdu_queue);
            return $this->parseSMS($pdu);
        }
        return false;
    }

    /**
     * Split the message if bigger than 160 chars. Can be executed only after bindTransmitter() call.
     */

    function split_message($text)
    {
        $max_len = 64;
        $res = array();
        if (strlen($text) <= $max_len) {
            $res[] = $text;
            return $res;
        }
        $pos = 0;
        $msg_sequence = $this->message_sequence++;
        $num_messages = ceil(strlen($text) / $max_len);
        $part_no = 1;
        while ($pos < strlen($text)) {
            $ttext = substr($text, $pos, $max_len);
            $pos += strlen($ttext);
            $udh = pack("cccccc", 5, 0, 3, $msg_sequence, $num_messages, $part_no);
            $part_no++;
            $res[] = $udh . $ttext;
        }
        return $res;
    }

    /**
     * Read one SMS from SMSC. Can be executed only after bindTransmitter() call.
     * @return true on succesfull send, false if error encountered
     */
    function sendSMS($from, $to, $message){
        if (strlen($from)>20 || strlen($to)>20) return false;
        if(($this->state!="bind_tx") && ($this->state!="bind_tcx"))return false;

        if (preg_match('/\D/', $from)) //alphanumeric sender
        {
            $this->sms_source_addr_ton = 5;
            $this->sms_source_addr_npi = 0;
        }
        //*
        $multi = $this->split_message($message);
        $multiple = (count($multi) > 1);
        if ($multiple) {
            if (!($this->sms_esm_class & 0x00000040))
                $this->sms_esm_class += 0x00000040;
        }
        reset($multi);

        while (list(, $part) = each($multi)) {
            //$message = $part;
            $pdu = pack('a1cca'.(strlen($from)+1).'cca'.(strlen($to)+1).'ccca1a1ccccca'.(strlen($part)),
                $this->sms_service_type,
                $this->sms_source_addr_ton,
                $this->sms_source_addr_npi,
                $from,//source_addr
                $this->sms_dest_addr_ton,
                $this->sms_dest_addr_npi,
                $to,//destination_addr
                $this->sms_esm_class,
                $this->sms_protocol_id,
                $this->sms_priority_flag,
                $this->sms_schedule_delivery_time,
                $this->sms_validity_period,
                $this->sms_registered_delivery_flag,
                $this->sms_replace_if_present_flag,
                $this->sms_data_coding,
                $this->sms_sm_default_msg_id,
                strlen($part),//sm_length
                $part//short_message
            );
            $status=$this->sendCommand(0x00000004,$pdu);
        }

        $this->message_sequence = rand(1,255);
        $this->sms_esm_class = 0;
        return ($status===0);
    }

    ////////////////private functions///////////////

    /**
     * @private function
     * Binds the socket and opens the session on SMSC
     * @param $login - ESME system_id
     * @param $port - ESME password
     * @return bind status or false on error
     */
    function _bind($login, $pass, $command_id){
        //make PDU
        $pdu = pack(
            'a'.(strlen($login)+1).
            'a'.(strlen($pass)+1).
            'a'.(strlen($this->system_type)+1).
            'CCCa'.(strlen($this->address_range)+1),
            $login, $pass, $this->system_type,
            $this->interface_version, $this->addr_ton,
            $this->addr_npi, $this->address_range);
        $status=$this->sendCommand($command_id,$pdu);
        return $status;
    }

    /**
     * @private function
     * Parse deliver PDU from SMSC.
     * @param $pdu - deliver PDU from SMSC.
     * @return parsed PDU as array.
     */
    function parseSMS($pdu){
        //check command id
        if($pdu['id']!=0x00000005)return false;
        //unpack PDU
        $ar=unpack("C*",$pdu['body']);
        $sms=array('service_type'=>$this->getString($ar,6),
            'source_addr_ton'=>array_shift($ar),
            'source_addr_npi'=>array_shift($ar),
            'source_addr'=>$this->getString($ar,21),
            'dest_addr_ton'=>array_shift($ar),
            'dest_addr_npi'=>array_shift($ar),
            'destination_addr'=>$this->getString($ar,21),
            'esm_class'=>array_shift($ar),
            'protocol_id'=>array_shift($ar),
            'priority_flag'=>array_shift($ar),
            'schedule_delivery_time'=>array_shift($ar),
            'validity_period'=>array_shift($ar),
            'registered_delivery'=>array_shift($ar),
            'replace_if_present_flag'=>array_shift($ar),
            'data_coding'=>$this->getdcs($ar),
            'sm_default_msg_id'=>array_shift($ar),
            'sm_length'=>$this->getlen($ar));

        /* check if the short message is inserted in the short_message field
         * or the optional message_payload field by checking the if length = 0
         */
        if($sms['sm_length'] == 0) {
            $i = 0;
            do {
                $tag = $length = $value = null;
                $tmp = '';
                $j = 0;
                // get the tag type
                do {
                    $tmp = array_shift($ar);
                    $tag .= str_pad(dechex($tmp),2,'0',STR_PAD_LEFT);
                    $j++;$i++;
                } while ($j < 2);

                $tag = strtoupper(str_pad($tag, 4,'0',STR_PAD_LEFT));

                if(isset($this->smpp_optional_param[$tag])) {
                    // get the length
                    $j = 0;
                    do {
                        $length .= array_shift($ar);
                        $j++;$i++;
                    } while ($j < 2);
                    $length = intval($length);
                    echo "length: $length|tag:" .$this->smpp_optional_param[$tag] . "\n" ;

                    // get the value
                    $j = 0;
                    $s = '';
                    do {
                        if($tag == '0424') { //messge payload start buillding message body
                            $c = array_shift($ar);
                            if ($c!=0) $s.= chr($c);
                        } else
                            $value .= array_shift($ar);
                        $j++;$i++;
                    } while ($j < $length);

                    //take care of deliver_sm segments, if any
                    switch($tag) {
                        case '020C': // if tag is 'sar_msg_ref_num' for deliver_sm
                            $current_ref = $value;
                            break;
                        case '020E': // if tag is 'sar_total_segments' for deliver_sm
                            if (!isset($this->deliver_sm_total_sgm[$current_ref]))
                                $this->deliver_sm_total_sgm[$current_ref] = $value;
                            break;
                        case '020F': // if tag is 'sar_segment_seqnum' for deliver_sm
                            if (isset($this->deliver_sm_total_sgm[$current_ref]))
                                $this->deliver_sm_total_sgm[$current_ref]--;
                            break;
                        case '0424':
                            if (isset($current_ref) && isset($this->deliver_sm_total_sgm[$current_ref]))
                                $this->deliver_sm_payloads[$current_ref] .= $s;
                            break;
                    }

                    // TODO translate the 'value' according to the optional parameter specs
                    if ($tag == '0424')  //case it is the payload
                        $sms[$this->smpp_optional_param[$tag]] = $s;
                    else
                        $sms[$this->smpp_optional_param[$tag]] = $value;
                }

            } while($i < 64000);
        }
        else
        {
            $sms['short_message'] = $this->gettext($ar);


        }

        // check if you accumlated a bunch of deliver_sm segments. Output this
        if(isset($this->deliver_sm_total_sgm[$current_ref])
            && ($this->deliver_sm_total_sgm[$current_ref] == 0)) {
            //assign the final body to the payload of the
            $sms['user_defined_payload'] = $this->deliver_sm_payloads[$current_ref];
            unset($this->deliver_sm_payloads[$current_ref]);
        }

        if($this->debug){
            echo "Delivered sms:\n";
            print_r($sms);
            echo "\n";
        }

        //send responce of recieving sms
        $this->sendPDU(0x80000005, "\0", $pdu['sn']);
        return $sms;
    }
    /**
     * @private function
     * Sends the PDU command to the SMSC and waits for responce.
     * @param $command_id - command ID
     * @param $pdu - PDU body
     * @return PDU status or false on error
     */
    function sendCommand($command_id, $pdu){
        if($this->state=="closed")return false;
        $this->sendPDU($command_id, $pdu, $this->sequence_number);
        $status=$this->readPDU_resp($this->sequence_number, $command_id);
        $this->sequence_number=$this->sequence_number+1;
        return $status;
    }
    /**
     * @private function
     * Prepares and sends PDU to SMSC.
     * @param $command_id - command ID
     * @param $pdu - PDU body
     * @param $seq_number - PDU sequence number
     */
    function sendPDU($command_id, $pdu, $seq_number){
        $length=strlen($pdu) + 16;
        $header=pack("NNNN", $length, $command_id, 0, $seq_number);
        if($this->debug){
            echo "Send PDU        : $length bytes\n";
            $this->printHex($header.$pdu);
            echo "command_id      : ".$command_id."\n";
            echo "sequence number : $seq_number\n\n";
        }

        fwrite($this->socket, $header.$pdu, $length);
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/logs/orderTake_sms_log.txt', $header.$pdu."\n", FILE_APPEND);
    }
    /**
     * @private function
     * Waits for SMSC responce on specific PDU.
     * @param $seq_number - PDU sequence number
     * @param $command_id - PDU command ID
     * @return PDU status or false on error
     */
    function readPDU_resp($seq_number, $command_id){
        //create responce id
        $command_id=$command_id|0x80000000;
        //check queue
        for($i=0;$i<count($this->pdu_queue);$i++){
            $pdu=$this->pdu_queue[$i];
            if($pdu['sn']==$seq_number && $pdu['id']==$command_id){
                //remove responce
                array_splice($this->pdu_queue, $i, 1);
                return $pdu['status'];
            }
        }
        //read pdu
        do{
            $pdu=$this->readPDU();
            if($pdu)array_push($this->pdu_queue, $pdu);
        }while($pdu && ($pdu['sn']!=$seq_number || $pdu['id']!=$command_id));
        //remove responce from queue
        if($pdu){
            array_pop($this->pdu_queue);
            return $pdu['status'];
        }
        return false;
    }
    /**
     * @private function
     * Reads incoming PDU from SMSC.
     * @return readed PDU or false on error.
     */
    function readPDU(){
        //read PDU length
        $tmp=fread($this->socket, 4);
        if(!$tmp)return false;
        extract(unpack("Nlength", $tmp));
        //read PDU headers
        $tmp2=fread($this->socket, 12);
        if(!$tmp2)return false;
        extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $tmp2));
        //read PDU body
        if($length-16>0){
            $body=fread($this->socket, $length-16);
            if(!$body)return false;
        }else{
            $body="";
        }
        if($this->debug){
            echo "Read PDU        : $length bytes\n";
            $this->printHex($tmp.$tmp2.$body);
            echo "body len        : " . strlen($body) . "\n";
            echo "Command id      : $command_id\n";
            echo "Command status  : $command_status\n";
            echo "sequence number : $sequence_number\n\n";
        }
        $pdu=array(
            'id'=>$command_id,
            'status'=>$command_status,
            'sn'=>$sequence_number,
            'body'=>$body);
        return $pdu;
    }
    /**
     * @private function
     * Reads C style zero padded string from the char array.
     * @param $ar - input array
     * @param $maxlen - maximum length to read.
     * @return readed string.
     */
    function getString(&$ar, $maxlen=255){
        $s="";
        $i=0;
        do{
            $c=array_shift($ar);
            $s.=chr($c);
            $i++;
        }	while($i<$maxlen && $c!=0);
        return $s;
    }


    function gettext(&$ar, $maxlen=255){
        $s="";
        $i=0;

        if ($this->dc==0)
        {		do{
            $c=array_shift($ar);
            $s.=chr($c);
            $i++;
        }	while($i<$this->smsle);
        }
        if ($this->dc==8)
        {		do{
            $c=array_shift($ar);
            $st=dechex($c);
            $c=array_shift($ar);
            $st.=dechex($c);

            $s.= $st ;

            $i++;
        }while($i<($this->smsle/2));
        }

        return $s;
    }



    function getdcs(&$ar){

        $c=array_shift($ar);
        $this->dc=$c;
        return $c;
    }

    function getlen(&$ar){

        $c=array_shift($ar);
        $this->smsle=$c;
        return $c;
    }


    /**
     * @private function
     * Prints the binary string as hex bytes.
     * @param $maxlen - maximum length to read.
     */
    function printHex($pdu){
        $ar=unpack("C*",$pdu);
        foreach($ar as $v){
            $s=dechex($v);
            if(strlen($s)<2)$s="0$s";
            print "$s ";
        }
        print "\n";
    }
}
?>