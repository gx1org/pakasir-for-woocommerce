<?php
if (!defined('ABSPATH')) {
    exit;
}
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return;
}

class WC_Gateway_Pakasir extends WC_Payment_Gateway {
    private $pakasir_slug;
    private $pakasir_redirect_url;
    private $pakasir_qris_only;
	private $pakasir_completed_status;

	public function __construct() {
        $this->id = 'pakasir';
        $this->method_title = 'Pakasir';
        $this->method_description = 'Pakasir Payment Gateway for WooCommerce';
        $this->has_fields = false;

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->pakasir_slug = $this->get_option('pakasir_slug');
        $this->pakasir_redirect_url = $this->get_option('pakasir_redirect_url');
        $this->pakasir_qris_only = $this->get_option('pakasir_qris_only');
        $this->pakasir_completed_status = $this->get_option('pakasir_completed_status');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
              'title' => 'Enable/Disable',
              'type' => 'checkbox',
              'label' => 'Aktifkan Pakasir',
              'default' => 'yes'
            ),
            'title' => array(
              'title' => 'Judul',
              'type' => 'text',
              'description' => 'Judul yang ditampilkan di halaman Checkout',
              'default' => 'Pakasir Payment Gateway',
              'desc_tip' => true
            ),
            'description' => array(
              'title' => 'Deskripsi',
              'type' => 'text',
              'description' => 'Deskripsi yang ditampilkan di halaman Checkout',
              'default' => 'Bayar dengan QRIS, Virtual Account, dll',
              'desc_tip' => true
            ),
            'pakasir_slug' => array(
              'title' => 'Pakasir Slug',
              'type' => 'text',
              'description' => 'Dapatkan di halaman detail proyek Pakasir.',
              'default' => '',
              'desc_tip' => true
            ),
            'pakasir_api_key' => array(
              'title' => 'Pakasir API Key',
              'type' => 'password',
              'description' => 'Dapatkan di halaman detail proyek Pakasir.',
              'default' => '',
              'desc_tip' => true
            ),
            'pakasir_redirect_url' => array(
              'title' => 'Redirect URL',
              'type' => 'text',
              'description' => 'Link redirect setelah pembayaran berhasil',
              'default' => get_site_url().'/my-account/view-order/{id}',
              'desc_tip' => true
            ),
            'pakasir_completed_status' => array(
              'title' => 'Complete status',
              'type' => 'select',
              'description' => 'Status order saat pembayaran berhasil',
              'default' => 'processing',
			  'options' => wc_get_order_statuses(),
              'desc_tip' => true
            ),
            'pakasir_qris_only' => array(
              'title' => 'QRIS Only',
              'type' => 'checkbox',
              'description' => 'Otomatis memunculkan kode QRIS dan tidak bisa ganti metode pembayaran lain',
              'label' => 'Hanya dapat dibayar dengan QRIS',
              'default' => 'no',
              'desc_tip' => true
            ),
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $amount = $order->get_total();
        $slug = $this->pakasir_slug;
        $redir = $this->pakasir_redirect_url;
		$redir = str_replace('{id}', $order_id, $redir);
        $qris = $this->pakasir_qris_only == 'yes' ? '&qris_only=1' : '';
        $url = "https://app.pakasir.com/pay/{$slug}/{$amount}/?order_id={$order_id}{$qris}&redirect={$redir}";

        $order->update_status( 'on-hold', 'Menunggu pembayaran Pakasir');
        WC()->cart->empty_cart();

        return array(
            'result' => 'success',
            'redirect' => $url,
        );
    }
}


// Add pakasir to WooCommerce payment gateways list
add_filter('woocommerce_payment_gateways', 'pakasir_add_gateway_class');
function pakasir_add_gateway_class($gateways) {
    $gateways[] = 'WC_Gateway_Pakasir';
    return $gateways;
}


// Function to handle webhook from Pakasir
function pakasir_webhook(WP_REST_Request $request) {
  $body = $request->get_json_params();
  $order_id = $body['order_id'] ?? null;
  if (!$order_id) {
      return new WP_REST_Response(['message' => 'Invalid order_id'], 400);
  }

  $order = wc_get_order($order_id);
  if (!$order) {
      return new WP_REST_Response(['message' => 'Order not found'], 404);
  }

  $order_id = $order->get_id();
  $amount = $order->get_total();
  $pakasir_settings = get_option('woocommerce_pakasir_settings');
  $slug = $pakasir_settings['pakasir_slug'];
  $api_key = $pakasir_settings['pakasir_api_key'];

  $url = "https://app.pakasir.com/api/transactiondetail?project=$slug&amount=$amount&order_id=$order_id&api_key=$api_key";
  $response = wp_remote_get($url);
  if (is_wp_error($response)) {
    return new WP_REST_Response(['message' => 'Cannot call pakasir API'], 500);
  }

  $response_body = json_decode(wp_remote_retrieve_body($response), true);
  if (!isset($response_body['transaction'])) {
    return new WP_REST_Response(['message' => $response_body['message']], 500);
  }

  $status = $response_body['transaction']['status'];
  if ($status != "completed") {
      return new WP_REST_Response(['message' => 'Transaction is not completed'], 400);
  }

  $order->payment_complete();
	
	$completed_status = isset($pakasir_settings['pakasir_completed_status']) ? $pakasir_settings['pakasir_completed_status'] : '';
	// kalau ada setting, update status order sesuai pilihan
	if ($completed_status) {
		$order->update_status($completed_status, 'Status diubah sesuai pengaturan Pakasir');
	}
	
  return new WP_REST_Response(['message' => 'Order updated'], 200);
}


function pakasir_verify_signature( WP_REST_Request $request ) {
    $signature = $request->get_header('x-signature');
    if ( $signature === md5('pakasir') ) {
        return true;
    }

    return new WP_Error(
        'forbidden',
        __('Invalid signature', 'pakasir-for-woocommerce'),
        ['status' => 403]
    );
}

// Endpoint to handle webhook from Pakasir: http://example.com/wp-json/pakasir/v1/webhook
add_action('rest_api_init', function() {
  register_rest_route('pakasir/v1', '/webhook', [
      'methods' => 'POST',
      'callback' => 'pakasir_webhook',
      'permission_callback' => 'pakasir_verify_signature',
  ]);
});
