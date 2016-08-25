<?php


class Setlr_Receipt {

    public function send_receipt( $customer_id, $project_id ) {
        $options= get_option( 'pdcrequest_settings');
        $user = get_userdata( $customer_id );
        
        $to = $user->user_email;
        $subject = __( '[Setlr.com] Receipt of Payment');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $html = '<html>';
        $html = '<head>';

        $html = '</head>';
        $html = '<body>';
        $html = '<div class="setlr-letterhead">';
        $html = esc_textarea( $options['pdcrequest_letterhead']);
        $html = '</div>';
        
        $html .= '<h1 class="header-title">' . __('RECEIPT OF PAYMENT', 'pdcrequest') . '</h1>';


        $html .= "Thank you, we have successfully received your payment. Here are the details for your records.";

        $html .= sprintf( __( 'Receipt No: %s', 'pdcrequest' ), $receipt_number );				
        $html .= sprintf( __( 'Date: %s', 'pdcrequest' ), $date );

        $html .= sprintf( __( 'Payment Received From: %s', 'pdcrequest' ), $customer_name );


        $html .= '<table>';
        $html .= '<caption>' . __( 'Services Provided:', 'pdcrequest') . '</caption>';
        $html .= '<tr>';
        $html .= '<td>' . $service_description . '</td>';
        $html .= '<td>' . $amount . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td>' . __( 'No VAT', 'pdcrequest') . '</td>';
        $html .= '<td>' . $vat_amount . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="setlr-receipt-total">' . __( 'Total', 'pdcrequest' ) . '</td>';
        $html .= '<td>' . $total . '</td>';
        $html .= '</tr>';
        $html .= '</table>';


        $html .= __( 'Payment received', 'pdcrequest' );

        $html .= __( 'Thanks again for using Setlr!', 'pdcrequest' );

        $html .= __( 'Important Note: Although we keep a record of this payment, we do not store credit or debit card information. Your payment was fully handled by PayPal for your security and convenience.', 'pdcrequest' );

        $html .= __( 'If you have any questions regarding this payment, please email pay@setlr.com, including the receipt number above and rest assured we’re here to help.', 'pdcrequest' );

        $html .= __( 'Sincerely,', 'pdcrequest' );

        $html .= esc_textarea( $options['pdcrequest_signature'] );

        
        
        $html .= '</body>';
        $html .= '</html>';
        $email_sent = wp_mail( $to, $subject, $html, $headers );
        
        return $email_sent;
        }
}