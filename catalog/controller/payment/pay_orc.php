<?php
/**
 * Extension name: PayOrc Payment
 * Descrption: Using this extension we will show payment methods on the checkout page.
 * Author: PayOrc Pvt. Ltd. 
 * 
 */
namespace Opencart\Catalog\Controller\Extension\PayOrc\Payment;

class PayOrc extends \Opencart\System\Engine\Controller
{
    /**
     * index
     *
     * @return mix
     */
    public function index(): string
    {

        $this->load->model('extension/pay_orc/payment/pay_orc');

        if (!$this->model_extension_pay_orc_payment_pay_orc->checkCredentials()) {
            return '';
        }

        $this->load->language('extension/pay_orc/payment/pay_orc');

        $data['language'] = $this->config->get('config_language');
        $data['testmode'] = $this->config->get('payment_pay_orc_test_mode') == 0 ? true : false;

        return $this->load->view('extension/pay_orc/payment/pay_orc', $data);
    }

    /**
     * confirm
     *
     * @return json|string
     */
    public function confirm(): void
    {
        // loading example payment language
        $this->load->language('extension/pay_orc/payment/pay_orc');

        $json = [];

        if (!isset($this->session->data['order_id'])) {
            $json['error'] = $this->language->get('error_order');
        }

        if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method']['code'] != 'pay_orc.pay_orc') {
            $json['error'] = $this->language->get('error_payment_method');
        }

        if (!$json) {
            $this->load->model('checkout/order');

            $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_pay_orc_order_status_id'));

            $json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function payment()
    {
        $this->load->model('extension/pay_orc/payment/pay_orc');

        $response = $this->model_extension_pay_orc_payment_pay_orc->create();

        if ($response['status'] === true) {
            $payorc_gate = json_decode($response['response'], 1);
            if ($payorc_gate['status'] == "SUCCESS") {
                die(json_encode(array('code' => 2, 'accept_payment' => $this->config->get('payment_pay_orc_accept_payment'), 'redirect' => $payorc_gate)));
            } else {
                die(json_encode(array('code' => 1, 'error' => array("message" => 'Error in payment, please contact support !'))));
            }
        } else {
            die(json_encode(array('code' => 1, 'error' => $response['response'])));
        }
    }

    public function validation() 
    {
        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);
            if ($order_info) {
                $this->session->data['customer_id'] = $order_info['customer_id'];
                $this->session->data['order_id'] = $order_id;
                $success = 1;

                if ($this->isValidOrder() === true) {
                    $payment_status = $this->config->get('payment_pay_orc_order_success_status_id');
                } else {
                    $success = 2;
                    $payment_status = $this->config->get('payment_pay_orc_order_fail_status_id');
                }

                $this->storePaymentInfo();

                $this->model_checkout_order->addHistory($order_id, (int) $payment_status);
                $success == 1 ? $this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true)) : $this->response->redirect($this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true));
            } else {
                $this->response->redirect($this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true));
            }
        } else {
            $this->response->redirect($this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true));
        }
    }

    public function embedValidation() 
    {
        if (isset($this->request->post['p_order_id'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if ($order_info) {
                $this->session->data['customer_id'] = $order_info['customer_id'];
                $success = 1;
                if ($this->isValidOrder() === true) {
                    $payment_status = $this->config->get('payment_pay_orc_order_success_status_id');
                } else {
                    $success = 2;
                    $payment_status = $this->config->get('payment_pay_orc_order_fail_status_id');
                }

                $this->storePaymentInfo();

                if ($success == 1) {

                    $this->model_checkout_order->addHistory($this->session->data['order_id'], (int) $payment_status);
                    die(json_encode(array('code' => 1, 'redirect_url' => $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true))));

                }  else {
                    
                    die(json_encode(array('code' => 2, 'redirect_url' => $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true))));
                }

            } else {
                die(json_encode(array('code' => 2, 'redirect_url' => $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true))));
            }
        } else {
            die(json_encode(array('code' => 2, 'redirect_url' => $this->url->link('checkout/failure', 'language=' . $this->config->get('config_language'), true))));
        }
    }

    protected function isValidOrder()
    {
        $status_code = $this->request->post["status_code"];
        $status = $this->request->post["status"];

        if ($status_code == 0 && $status == "SUCCESS") {
            return true;
        }

        return false;
    }

    protected function storePaymentInfo()
    {
        $this->load->model('checkout/cart');
        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $cart_id = $product['cart_id'];
        }
        $data = [
            'type' => 'payment',
            'source_type' => 'card',
            'p_request_id' => isset($this->request->post['p_request_id']) ? $this->request->post['p_request_id'] : null,
            'm_payment_token' => isset($this->request->post['m_payment_token']) ? $this->request->post['m_payment_token'] : null,
            'p_order_id' => isset($this->request->post['p_order_id']) ? $this->request->post['p_order_id'] : null,
            'id_customer' => isset($this->request->post['m_customer_id']) ? $this->request->post['m_customer_id'] : null,
            'id_cart' => $cart_id,
            'id_order' => $this->session->data["order_id"],
            'transaction_id' => isset($this->request->post['transaction_id']) ? $this->request->post['transaction_id'] : null,
            'amount' => isset($this->request->post['amount']) ? $this->request->post['amount'] : null,
            'status' => isset($this->request->post['status']) ? $this->request->post['status'] : 'pending',
            'response' => isset($this->request->post) ? json_encode($this->request->post) : '',
            'currency' => isset($this->request->post['currency']) ? $this->request->post['currency'] : null,
            'cc_schema' => isset($this->request->post['payment_method_data']['scheme']) ? $this->request->post['payment_method_data']['scheme'] : null,
            'cc_type' => isset($this->request->post['payment_method_data']['card_type']) ? $this->request->post['payment_method_data']['card_type'] : null,
            'cc_mask' => isset($this->request->post['payment_method_data']['mask_card_number']) ? $this->request->post['payment_method_data']['mask_card_number'] : null,
            'mode' => isset($this->request->post['mode']) ? $this->request->post['mode'] : 'test',
            'date_add' => date('Y-m-d H:i:s'),
        ];

        $this->load->model('extension/pay_orc/payment/pay_orc');

        $this->model_extension_pay_orc_payment_pay_orc->processPayment($data, $this->request->post['p_order_id']);
    }
}