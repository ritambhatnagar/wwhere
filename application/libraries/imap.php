<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class imap {

    function getConnected($server, $username, $pass) {

        return imap_open($server, $username, $pass);
    }

    function cimap_num_msg($connection) {
        return imap_num_msg($connection);
    }

    function cimap_get_quota($connection) {
        $storage = $quota['STORAGE'] = imap_get_quotaroot($connection, "INBOX");

        function kilobyte($filesize) {
            return round($filesize / 1024, 2) . ' Mb';
        }

        return kilobyte($storage['usage']) . ' / ' . kilobyte($storage['limit']) . ' (' . round($storage['usage'] / $storage['limit'] * 100, 2) . '%)';
    }

    function bounce_mail($connection) {

        $mail = imap_search($connection, 'SUBJECT "Delivery Status Notification (Failure)"');

        foreach ($mail as $key => $value) {
            $body = imap_headerinfo($connection, $value);

            $info[$key] = array(
                'Subject' => $body->Subject
            );
        }

        return $info;
    }

}