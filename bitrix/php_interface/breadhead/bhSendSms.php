<?
define("CM_BIND_TRANSMITTER", 0x00000009);
//define("CM_BIND_TRANSMITTER", 0x00000002);
define("CM_SUBMIT_SM", 0x00000004);
define("CM_DELIVER_SM", 0x00000005);
define("CM_UNBIND", 0x00000006);
class bhSendSms {
// public members:
    var $msg_id;
    function SMPPClass()
    {
        $this->_socket = NULL;
        $this->_command_status = 0;
        $this->_sequence_number = 1;
        $this->_source_address = "";
        $this->msg_id="";
    }
    function SetSender($from)
    {
        if (strlen($from) > 20) {
            // todo
            echo "Error: sender id too long.<br>\n";
            return;
        }
        $this->_source_address = $from;
    }
    /* Метод, предназначенный для инициализации сессии:*/
    function Start($host, $port, $username, $password, $system_type)
    {
        $this->_socket = fsockopen($host, $port, $errno, $errstr, 1.2);
        // todo: sanity check on input parameters
        if (!$this->_socket) {
            echo "Error opening SMPP session.<br>\n";
            echo "Error was: $errstr.<br>\n";
            return;
        }
        //socket_set_timeout($this->_socket, 1200);
        $status = $this->SendBindTransmitter($username, $password, $system_type);
        if ($status != 0) {
            echo "Error binding to SMPP server. Invalid credentials?<br>\n";
        }
    }
    /*Метод, используемый для отправки единичного сообщения*/
    function Send($to, $text)
    {
        if (strlen($to) > 20) {
            echo "to-address too long.<br>\n";
            return;
        }
        if (strlen($text) > 160) {
            echo "Message too long.<br>\n";
            return;
        }
        if (!$this->_socket) {
            // not connected
            return;
        }
        $service_type = "";
        $source_addr_ton = 0;//default 0
        $source_addr_npi = 3;//0
        $source_addr = $this->_source_address;
        $dest_addr_ton = 1;
        $dest_addr_npi = 1;
        $destination_addr = $to;
        $esm_class = 0;
        $protocol_id = 0;
        $priority_flag = 0;
        $schedule_delivery_time = "";
        $validity_period = "";
        $registered_delivery_flag = 1;
        $replace_if_present_flag = 0;
        $data_coding = 8;
        $sm_default_msg_id = 0;
        $sm_length = strlen($text);
        $short_message = $text;
        $status = $this->SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr,
            $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time,
            $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length,
            $short_message);
        if ($status != 0) {
            echo "SMPP server returned error $status.<br>\n";
        }
    }
    /*Метод, закрывающий SMPP-сессию*/
    function End()
    {
        if (!$this->_socket) {
            // not connected
            return;
        }
        $status = $this->SendUnbind();
        if ($status != 0) {
            echo "SMPP Server returned error $status.<br>\n";
        }
        fclose($this->_socket);
        $this->_socket = NULL;
    }
// private members (not documented):
    function ExpectPDU($our_sequence_number)
    {
        do {
            $elength = fread($this->_socket, 4);
            extract(unpack("Nlength", $elength));
            $stream = fread($this->_socket, $length - 4);
            echo "Read PDU : $length bytes.<br>\n";
            echo "Stream len : " . strlen($stream) . "<br>\n";
            extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $stream));
            $command_id &= 0x0fffffff;
            echo "Command id : $command_id.<br>\n";
            echo "Command status : $command_status.<br>\n";
            echo "sequence_number : $sequence_number.<br>\n";
            switch ($command_id) {
                case CM_BIND_TRANSMITTER:
                    echo "Got CM_BIND_TRANSMITTER_RESP.<br>\n";
                    break;
                case CM_UNBIND:
                    echo "Got CM_UNBIND_RESP.<br>\n";
                    break;
                case CM_SUBMIT_SM:
                    echo "Got CM_SUBMIT_SM_RESP.<br>\n";
                    $stream=substr($stream,12,strlen($stream)-12);
                    echo "MSG ID : $stream.<br>\n";
                    break;
                case CM_DELIVER_SM:
                    echo "Got CM_DELIVER_SM_RESP<br>\n";
                    $stream=substr($stream,12,strlen($stream)-12);
                    echo "MSG ID : $stream.<br>\n";
                    break;
                default:
                    echo "Got unknown SMPP pdu.<br>\n";
                    break;
            }
        } while ($sequence_number != $our_sequence_number);
        return $command_status;
    }
    function SendPDU($command_id, $pdu)
    {
        $length = strlen($pdu) + 16;
        $header = pack("NNNN", $length, $command_id, $this->_command_status, $this->_sequence_number);
        echo "Sending PDU, len == $length<br>\n";
        echo "Sending PDU, header-len == " . strlen($header) . "<br>\n";
        echo "Sending PDU, command_id == " . $command_id . "<br>\n";
        fwrite($this->_socket, $header . $pdu, $length);
        $status = $this->ExpectPDU($this->_sequence_number);
        $this->_sequence_number = $this->_sequence_number + 1;
        return $status;
    }
    function SendBindTransmitter($system_id, $smpppassword, $system_type)
    {
        $system_id_len = strlen($system_id) + 1;
        $smpppassword_len = strlen($smpppassword) + 1;
        $system_type_len = strlen($system_type) + 1;
        $pdu = pack("a{$system_id_len}a{$smpppassword_len}a{$system_type_len}CCCa1", $system_id, $smpppassword,
            $system_type, 3, 0, 0, "");
        $status = $this->SendPDU(CM_BIND_TRANSMITTER, $pdu);
        return $status;
    }
    function SendUnbind()
    {
        $pdu = "";
        $status = $this->SendPDU(CM_UNBIND, $pdu);
        return $status;
    }
    function SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message)
    {
        $service_type_len = strlen($service_type) + 1;
        $source_addr_len = strlen($source_addr) + 1;
        $destination_addr_len = strlen($destination_addr) + 1;
        $schedule_delivery_time_len = strlen($schedule_delivery_time) + 1;
        $validity_period_len = strlen($validity_period) + 1;
        $message_len = $sm_length + 1;
        echo "PDU spec:a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}<br>\n";
        $pdu = pack("a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}", $service_type, $source_addr_ton, $source_addr_npi, $source_addr,$dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time,
        $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
        $status = $this->SendPDU(CM_SUBMIT_SM, $pdu);
        return $status;
 }
    function Deliver_SM($to,$text)
    {
        $service_type = "";
        $source_addr_ton = 0;//default 0
        $source_addr_npi = 3;//0
        $source_addr = $this->_source_address;
        $dest_addr_ton = 1;
        $dest_addr_npi = 1;
        $destination_addr = $to;
        $esm_class = 0;
        $protocol_id = 0;
        $priority_flag = 0;
        $schedule_delivery_time = "";
        $validity_period = "";
        $registered_delivery_flag = 0;
        $replace_if_present_flag = 0;
        $data_coding = 8;
        $sm_default_msg_id = 0;
        $sm_length = strlen($text);
        $short_message = $text;
        $service_type_len = strlen($service_type) + 1;
        $source_addr_len = strlen($source_addr) + 1;
        $destination_addr_len = strlen($destination_addr) + 1;
        $schedule_delivery_time_len = strlen($schedule_delivery_time) + 1;
        $validity_period_len = strlen($validity_period) + 1;
        $message_len = $sm_length + 1;
        echo "PDU spec:a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}<br>\n"; $pdu = pack("a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}", $service_type, $source_addr_ton, $source_addr_npi, $source_addr,
$dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time,$validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length,$short_message);
 $status = $this->SendPDU(CM_DELIVER_SM, $pdu);
 return $status;
}
};
?>