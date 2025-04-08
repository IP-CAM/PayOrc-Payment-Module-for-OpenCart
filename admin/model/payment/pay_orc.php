<?php
namespace Opencart\Admin\Model\Extension\PayOrc\Payment;

class PayOrc extends \Opencart\System\Engine\Model
{

    public function install(): void
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'payorc_transaction` (
            `id_payorc` int(11) NOT NULL AUTO_INCREMENT,
            `type` enum("payment","refund") NOT NULL DEFAULT "payment",
            `source_type` varchar(16) NOT NULL DEFAULT "card",
            `p_request_id` VARCHAR(100), 
            `m_payment_token` varchar(120),
            `p_order_id` VARCHAR(100),
            `id_customer` int(10), 
            `id_cart` int(10), 
            `id_order` int(10), 
            `transaction_id` varchar(32), 
            `amount` float(20,6), 
            `status` varchar(32) NOT NULL DEFAULT "pending", 
            `response` TEXT NULL, 
            `currency` varchar(3), 
            `cc_schema` varchar(16),
            `cc_type` varchar(16), 
            `cc_mask` varchar(30), 
            `mode` enum("live","test"), 
            `date_add` datetime, 
            PRIMARY KEY (`id_payorc`)
        ) AUTO_INCREMENT=1');
    }

    public function uninstall(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "payorc_transaction`");
    }

    public function getPaymentActionType(): array
    {
        $action_types = [];
        $action_types[] = [
            'action_type_id' => '1',
            'action_type_name' => 'AUTH',
        ];
        $action_types[] = [
            'action_type_id' => '2',
            'action_type_name' => 'SALE',
        ];
        return $action_types;
    }

    public function getPaymentSolution(): array
    {
        $payment_solutions = [];
        $payment_solutions[] = [
            'solution_id' => '1',
            'solution_name' => 'PayOrc Embedded Solution',
        ];
        $payment_solutions[] = [
            'solution_id' => '2',
            'solution_name' => 'PayOrc Hosted Solution',
        ];
        return $payment_solutions;
    }

    public function getPaymentCaptureMethod(): array
    {
        $capture_methods = [];
        $capture_methods[] = [
            'catpture_id' => '1',
            'catpture_name' => 'MANUAL',
        ];
        $capture_methods[] = [
            'catpture_id' => '2',
            'catpture_name' => 'AUTOMATIC',
        ];
        return $capture_methods;
    }

    public function getTransaction($order_id): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payorc_transaction` WHERE id_order = '" . (int)$order_id . "' ORDER BY id_payorc DESC LIMIT 1");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return [];
    }

    public function getTransactions(): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payorc_transaction` GROUP BY p_order_id ORDER BY id_payorc DESC");
        
        if ($query->num_rows) {
            return $query->rows;
        }
        
        return [];
    }

    public function getCustomers($id_customer) 
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$id_customer . "'");
        if ($query->num_rows) {
            return $query->row;
        }
        
        return [];
    }
}