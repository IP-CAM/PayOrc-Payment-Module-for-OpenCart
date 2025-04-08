<?php
/**
 * Extension name: Payorc Payment
 * Descrption: Using this extension we will show payment methods on the checkout page.
 * Author: Payorc Pvt. Ltd. 
 * 
 */
namespace Opencart\Admin\Controller\Extension\PayOrc\Payment;

class PayOrc extends \Opencart\System\Engine\Controller
{

    /**
     * index
     *
     * @return void
     */
    public function index(): void
    {

        $this->load->language('extension/pay_orc/payment/pay_orc');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
        ];

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/pay_orc/payment/pay_orc', 'user_token=' . $this->session->data['user_token'])
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/pay_orc/payment/pay_orc', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
            ];
        }

        $data['save'] = $this->url->link('extension/pay_orc/payment/pay_orc.save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        $this->load->model('extension/pay_orc/payment/pay_orc');

        // getting payment extension config
        $data['payment_pay_orc_order_success_status_id'] = $this->config->get('payment_pay_orc_order_success_status_id');
        $data['payment_pay_orc_order_fail_status_id'] = $this->config->get('payment_pay_orc_order_fail_status_id');
        $data['payment_pay_orc_test_merchant_id'] = $this->config->get('payment_pay_orc_test_merchant_id');
        $data['payment_pay_orc_live_merchant_id'] = $this->config->get('payment_pay_orc_live_merchant_id');
        $data['payment_pay_orc_test_secret_key'] = $this->config->get('payment_pay_orc_test_secret_key');
        $data['payment_pay_orc_live_secret_key'] = $this->config->get('payment_pay_orc_live_secret_key');
        $data['payment_pay_orc_test_mode'] = $this->config->get('payment_pay_orc_test_mode');
        $data['action_types'] = $this->model_extension_pay_orc_payment_pay_orc->getPaymentActionType();
        $data['payment_solutions'] = $this->model_extension_pay_orc_payment_pay_orc->getPaymentSolution();
        $data['capture_methods'] = $this->model_extension_pay_orc_payment_pay_orc->getPaymentCaptureMethod();
        $data['payment_pay_orc_action_type'] = $this->config->get('payment_pay_orc_action_type');
        $data['payment_pay_orc_accept_payment'] = $this->config->get('payment_pay_orc_accept_payment');
        $data['payment_pay_orc_capture_mode'] = $this->config->get('payment_pay_orc_capture_mode');

        // loading order status model
        $this->load->model('localisation/order_status');

        // getting order status as array
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // zeo zone id
        $data['payment_pay_orc_geo_zone_id'] = $this->config->get('payment_pay_orc_geo_zone_id');

        // loading geo_zone model
        $this->load->model('localisation/geo_zone');

        // getting all zeo zones
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['payment_pay_orc_status'] = $this->config->get('payment_pay_orc_status');
        $data['payment_pay_orc_sort_order'] = $this->config->get('payment_pay_orc_sort_order');

        $transactions = $this->model_extension_pay_orc_payment_pay_orc->getTransactions();

        $data['transactions'] = array();

        if(!empty($transactions)) {
            foreach($transactions as $transaction) {
                $customer = $this->model_extension_pay_orc_payment_pay_orc->getCustomers((int) $transaction['id_customer']);
                $data['transactions'][$transaction["id_payorc"]] = array(
                    'id_payorc' => $transaction["id_payorc"],
                    'p_order_id' => $transaction["p_order_id"],
                    'customer_email' => $customer['email'],
                    'id_order' => $transaction['id_order'],
                    'transaction_id' => $transaction['transaction_id'],
                    'paid_amount' => $this->currency->format((float)$transaction['amount'], $this->config->get('config_currency')),
                    'status' => $transaction['status'],
                    'response' => $transaction['response'],
                    'date_add' => $transaction['date_add'],
                );
            }   
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Dynamically determine the admin path
        $admin_path = str_replace(HTTP_SERVER, '', HTTP_CATALOG);
        $data['domain'] = str_replace($admin_path, '', HTTP_SERVER);
        $data['domain'] = str_replace('/qzpqkgtun63cl1jz', '', HTTP_SERVER);

        // Add user token to data array
        $data['user_token'] = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('extension/pay_orc/payment/pay_orc', $data));
    }

    /**
     * save method
     *
     * @return void
     */
    public function save(): void
    {
        // loading example payment language
        $this->load->language('extension/pay_orc/payment/pay_orc');

        $json = [];

        // checking file modification permission
        if (!$this->user->hasPermission('modify', 'extension/pay_orc/payment/pay_orc')) {
            $json['error']['warning'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('payment_pay_orc', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void
    {
        if ($this->user->hasPermission('modify', 'extension/pay_orc/payment/pay_orc')) {
            $this->load->model('extension/pay_orc/payment/pay_orc');
            $this->model_extension_pay_orc_payment_pay_orc->install();
        }
    }

    public function uninstall(): void
    {
        if ($this->user->hasPermission('modify', 'extension/pay_orc/payment/pay_orc')) {
            $this->load->model('extension/pay_orc/payment/pay_orc');
            $this->model_extension_pay_orc_payment_pay_orc->uninstall();
        }
    }

    public function getTransaction(): void {
        $json = [];
        
        // Check user has permission
        if (!$this->user->hasPermission('modify', 'extension/pay_orc/payment/pay_orc')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->get['order_id'])) {
                $this->load->model('extension/pay_orc/payment/pay_orc');
                
                $transaction = $this->model_extension_pay_orc_payment_pay_orc->getTransaction($this->request->get['order_id']);
                
                if ($transaction) {
                    $json['success'] = true;
                    $json['transaction'] = $transaction;
                } else {
                    $json['success'] = false;
                    $json['error'] = 'No transaction found for this order ID';
                }
            } else {
                $json['success'] = false;
                $json['error'] = 'Order ID is required';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}