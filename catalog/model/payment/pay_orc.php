<?php
/**
 * Extension name: PayOrc Payment
 * Descrption: Using this extension we will show payment methods on the checkout page.
 * Author: PayOrc Pvt. Ltd. 
 * 
 */
namespace Opencart\Catalog\Model\Extension\PayOrc\Payment;

class PayOrc extends \Opencart\System\Engine\Model
{

    public $api_url = "https://nodeserver.payorc.com/api/v1";

    protected $countries=["AW"=>"297","AF"=>"93","AO"=>"244","AI"=>"1-264","AX"=>"358-18","AL"=>"355","AD"=>"376","AR"=>"54","AM"=>"374","AS"=>"1-684","AQ"=>"672","TF"=>"262","AG"=>"1-268","AU"=>"61","AT"=>"43","AZ"=>"994","BI"=>"257","BE"=>"32","BJ"=>"229","BQ"=>"599","BF"=>"226","BD"=>"880","BG"=>"359","BH"=>"973","BS"=>"1-242","BA"=>"387","BL"=>"590","BY"=>"375","BZ"=>"501","BM"=>"1-441","BO"=>"591","BR"=>"55","BB"=>"1-246","BN"=>"673","BT"=>"975","BV"=>"47","BW"=>"267","CF"=>"236","CA"=>"1","CC"=>"61","CH"=>"41","CL"=>"56","CN"=>"86","CI"=>"225","CM"=>"237","CD"=>"243","CG"=>"242","CK"=>"682","CO"=>"57","KM"=>"269","CV"=>"238","CR"=>"506","CU"=>"53","CW"=>"599","CX"=>"61","KY"=>"1-345","CY"=>"357","CZ"=>"420","DE"=>"49","DJ"=>"253","DM"=>"1-767","DK"=>"45","DO"=>"1-809","DZ"=>"213","EC"=>"593","EG"=>"20","ER"=>"291","EH"=>"212","ES"=>"34","EE"=>"372","ET"=>"251","FI"=>"358","FJ"=>"679","FK"=>"500","FR"=>"33","FO"=>"298","FM"=>"691","GA"=>"241","GB"=>"44","GE"=>"995","GG"=>"44","GH"=>"233","GI"=>"350","GN"=>"224","GP"=>"590","GM"=>"220","GW"=>"245","GQ"=>"240","GR"=>"30","GD"=>"1-473","GL"=>"299","GT"=>"502","GF"=>"594","GU"=>"1-671","GY"=>"592","HK"=>"852","HM"=>"61","HN"=>"504","HR"=>"385","HT"=>"509","HU"=>"36","ID"=>"62","IM"=>"44","IN"=>"91","IO"=>"246","IE"=>"353","IR"=>"98","IQ"=>"964","IS"=>"354","IL"=>"972","IT"=>"39","JM"=>"1-876","JE"=>"44","JO"=>"962","JP"=>"81","KZ"=>"7","KE"=>"254","KG"=>"996","KH"=>"855","KI"=>"686","KN"=>"1-869","KR"=>"82","KW"=>"965","LA"=>"856","LB"=>"961","LR"=>"231","LY"=>"218","LC"=>"1-758","LI"=>"423","LK"=>"94","LS"=>"266","LT"=>"370","LU"=>"352","LV"=>"371","MO"=>"853","MF"=>"590","MA"=>"212","MC"=>"377","MD"=>"373","MG"=>"261","MV"=>"960","MX"=>"52","MH"=>"692","MK"=>"389","ML"=>"223","MT"=>"356","MM"=>"95","ME"=>"382","MN"=>"976","MP"=>"1-670","MZ"=>"258","MR"=>"222","MS"=>"1-664","MQ"=>"596","MU"=>"230","MW"=>"265","MY"=>"60","YT"=>"262","NA"=>"264","NC"=>"687","NE"=>"227","NF"=>"672","NG"=>"234","NI"=>"505","NU"=>"683","NL"=>"31","NO"=>"47","NP"=>"977","NR"=>"674","NZ"=>"64","OM"=>"968","PK"=>"92","PA"=>"507","PN"=>"64","PE"=>"51","PH"=>"63","PW"=>"680","PG"=>"675","PL"=>"48","PR"=>"1-787","KP"=>"850","PT"=>"351","PY"=>"595","PS"=>"970","PF"=>"689","QA"=>"974","RE"=>"262","RO"=>"40","RU"=>"7","RW"=>"250","SA"=>"966","SD"=>"249","SN"=>"221","SG"=>"65","GS"=>"500","SH"=>"290","SJ"=>"47","SB"=>"677","SL"=>"232","SV"=>"503","SM"=>"378","SO"=>"252","PM"=>"508","RS"=>"381","SS"=>"211","ST"=>"239","SR"=>"597","SK"=>"421","SI"=>"386","SE"=>"46","SZ"=>"268","SX"=>"1-721","SC"=>"248","SY"=>"963","TC"=>"1-649","TD"=>"235","TG"=>"228","TH"=>"66","TJ"=>"992","TK"=>"690","TM"=>"993","TL"=>"670","TO"=>"676","TT"=>"1-868","TN"=>"216","TR"=>"90","TV"=>"688","TW"=>"886","TZ"=>"255","UG"=>"256","UA"=>"380","UM"=>"1","UY"=>"598","US"=>"1","UZ"=>"998","VA"=>"379","VC"=>"1-784","VE"=>"58","VG"=>"1-284","VI"=>"1-340","VN"=>"84","VU"=>"678","WF"=>"681","WS"=>"685","YE"=>"967","ZA"=>"27","ZM"=>"260","ZW"=>"263",];

    /**
     * getMethods
     *
     * @param  mixed $address
     * @return array
     */
    public function getMethods(array $address = []): array
    {

        // loading example payment language
        $this->load->language('extension/pay_orc/payment/pay_orc');

        if ($this->cart->hasSubscription()) {
            $status = false;
        } elseif (!$this->cart->hasShipping()) {
            $status = false;
        } elseif (!$this->config->get('config_checkout_payment_address')) {
            $status = true;
        } elseif (!$this->config->get('payment_pay_orc_geo_zone_id')) {
            $status = true;
        } else {
            // getting payment data using zeo zone
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int) $this->config->get('payment_pay_orc_geo_zone_id') . "' AND `country_id` = '" . (int) $address['country_id'] . "' AND (`zone_id` = '" . (int) $address['zone_id'] . "' OR `zone_id` = '0')");

            // if the rows found the status set to True
            if ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        }

        $method_data = [];

        if ($status) {
            $option_data['pay_orc'] = [
                'code' => 'pay_orc.pay_orc',
                'name' => $this->language->get('heading_title')
            ];

            $method_data = [
                'code' => 'pay_orc',
                'name' => $this->language->get('heading_title'),
                'option' => $option_data,
                'sort_order' => $this->config->get('payment_pay_orc_sort_order')
            ];
        }

        return $method_data;
    }

    public function checkCredentials()
    {
        $merchent_key = $this->config->get('payment_pay_orc_live_merchant_id');
        $merchent_secret = $this->config->get('payment_pay_orc_live_secret_key');
        $env = 'live';
        if ($this->config->get('payment_pay_orc_test_mode') == 0) {
            $merchent_key = $this->config->get('payment_pay_orc_test_merchant_id');
            $merchent_secret = $this->config->get('payment_pay_orc_test_secret_key');
            $env = 'test';
        }
        $data = array(
            'merchant_key' => $merchent_key,
            'merchant_secret' => $merchent_secret,
            'env' => $env
        );

        $response = $this->makeCurlRequest($this->api_url . "/check/keys-secret", "POST", $data, 1);
        if ($response['status'] === true) {
            return true;
        }
        return false;
    }

    public function create()
    {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $order_details = array(
            "m_order_id" => "",
            "amount" => $order_info['total'] - $order_info['shipping_method']['cost'],
            "convenience_fee" => "0",
            "currency" => $order_info['currency_code'],
            "description" => "",
            "quantity" => 1
        );
        $customer_details = array(
            'name' => $order_info['firstname'] . " " . $order_info['lastname'],
            'm_customer_id' => $order_info['customer_id'],
            'email' => $order_info['email'],
            'mobile' => $order_info['telephone'],
            'code' => isset($this->countries[$order_info['currency_code']]) && !empty($order_info['telephone']) ? $this->countries[$order_info['currency_code']] : "",
        );
        $billing_details = array(
            'address_line1' => $order_info['shipping_address_1'],
            'address_line2' => $order_info['shipping_address_2'],
            'city' => $order_info['shipping_city'],
            'province' => $order_info['shipping_city'],
            'pin' => $order_info['shipping_postcode'],
            'country' => $order_info['shipping_iso_code_2'],
        );
        $shipping_details = array(
            'shipping_name' => $order_info['shipping_method']['name'],
            'shipping_email' => "",
            'shipping_code' => isset($this->countries[$order_info['currency_code']]) && !empty($order_info['telephone']) ? $this->countries[$order_info['currency_code']] : "",
            'shipping_mobile' => !empty($order_info['telephone']) ? $order_info['telephone'] : "",
            'address_line1' => $order_info['shipping_address_1'],
            'address_line2' => $order_info['shipping_address_2'],
            'city' => $order_info['shipping_city'],
            'province' => $order_info['shipping_city'],
            'pin' => $order_info['shipping_postcode'],
            'country' => $order_info['shipping_iso_code_2'],
            "location_pin" => "https://location/somepoint",
            "shipping_currency" => $order_info['currency_code'],
            "shipping_amount" => $order_info['shipping_method']['cost'],
        );
        $success_url = "";
        $cancel_url = "";
        if ($this->config->get('payment_pay_orc_accept_payment') == 2) {
            $this->load->model('checkout/cart');
            $products = $this->cart->getProducts();
            foreach ($products as $product) {
                $cart_id = $product['cart_id'];
            }
            $success_url = $this->url->link('extension/pay_orc/payment/pay_orc.validation', 'id_cart='.$cart_id."&order_id=".$order_info['order_id'], true);
            $cancel_url = $this->url->link('checkout/checkout', '', true);
        }
        $action = "AUTH";
        if ($this->config->get('payment_pay_orc_action_type') == 2) {
            $action = "SALE";
        }
        $capture_method = $this->config->get('payment_pay_orc_capture_mode');
        $method = "MANUAL";
        if ($capture_method == 2) {
            $method = "AUTOMATIC";
        }
        $platform = array(
            "platform" => $this->request->get['platform'],
            "browser" => $this->request->get['browserName'],
            "browser-version" => $this->request->get['browserVersion'],
        );
        $po_order = [
            "data" => [
                "class" => "ECOM",
                "action" => $action,
                "capture_method" => $method,
                "payment_token" => "",
                "order_details" => $order_details,
                "customer_details" => $customer_details,
                "billing_details" => $billing_details,
                "shipping_details" => $shipping_details,
                "urls" => [
                    "success" => $success_url,
                    "cancel" => $cancel_url,
                    "failure" => $success_url
                ],
                'parameters' => [
                    ['alpha' => ''],
                    ['beta' => ''],
                    ['gamma' => ''],
                    ['delta' => ''],
                    ['epsilon' => '']
                ],
                'custom_data' =>[
                    ['alpha' => ''],
                    ['beta' => ''],
                    ['gamma' => ''],
                    ['delta' => ''],
                    ['epsilon' => '']
                ]
            ]
        ];
        return $this->makeCurlRequest($this->api_url . "/sdk/orders/create", "POST", $po_order, 0, $platform);
    }

    public function processPayment($data, $p_order_id)
    { 
        $columns = implode(', ', array_keys($data));
        $values = array_map(function($value) {
            return is_null($value) ? 'NULL' : "'" . $this->db->escape($value) . "'";
        }, $data);
        $values = implode(', ', $values);

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payorc_transaction` WHERE `p_order_id` = '" . (int) $p_order_id . "'");

        if ($query->num_rows) {
            return true;            
        }

        $query = "INSERT INTO `" . DB_PREFIX . "payorc_transaction` ($columns) VALUES ($values)";
        $result = $this->db->query($query);
        
        return $result;
    }

    /**
     * Common cURL request function
     * 
     * @param string $url - The URL to send the request to
     * @param string $method - HTTP method (GET or POST)
     * @param array|null $data - Data to send with the request (for POST only)
     * @param array|null $headers - request headers (for POST only)
     * @return array - The response from the cURL request
     */
    public function makeCurlRequest($url, $method = "GET", $data = null, $auth = 1, $platform = [])
    {
        $merchent_key = $this->config->get('payment_pay_orc_live_merchant_id');
        $merchent_secret = $this->config->get('payment_pay_orc_live_secret_key');
        if ($this->config->get('payment_pay_orc_test_mode') == 0) {
            $merchent_key = $this->config->get('payment_pay_orc_test_merchant_id');
            $merchent_secret = $this->config->get('payment_pay_orc_test_secret_key');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($auth == 1) {
            $headers = [
                "Content-Type: application/json",
            ];
        }

        if ($auth == 0) {
            $headers = [
                "sdk: OpenCart",
                "sdk-version: " . VERSION,
                "Content-Type: application/json",
                "merchant-key: " . $merchent_key,
                "merchant-secret: " . $merchent_secret,
                "platform:" . $platform['platform'],
                "browser:" . $platform['browser'],
                "browser-version:" . $platform['browser-version'],
            ];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method == "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpcode != 200) {
            return array('status' => false, 'response' => $response);
        }

        return array('status' => true, 'response' => $response);
    }
}