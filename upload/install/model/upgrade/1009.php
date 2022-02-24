<?php
class ModelUpgrade1009 extends Model {
	public function upgrade() {
		// Address
		$address_info = $this->db->query("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "address' AND COLUMN_NAME = 'company'");
		
		if ($address_info->num_rows && $address_info->row['CHARACTER_MAXIMUM_LENGTH'] && $address_info->row['CHARACTER_MAXIMUM_LENGTH'] < 60) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "address` MODIFY COLUMN `company` VARCHAR(60)");
		}

		// Affiliate customer merge code
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate'");

		if ($query->num_rows) {
			// Removing affiliate and moving to the customer account.
			$config = new Config();
			
			$setting_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0'");
			
			foreach ($setting_query->rows as $setting) {
				$config->set($setting['key'], $setting['value']);
			}
			
			$affiliate_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate`");
			
			foreach ($affiliate_query->rows as $affiliate) {
				$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `email` = '" . $this->db->escape($affiliate['email']) . "'");
				
				if (!$customer_query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer` SET `customer_group_id` = '" . (int)$config->get('config_customer_group_id') . "', `language_id` = '" . (int)$config->get('config_customer_group_id') . "', `firstname` = '" . $this->db->escape($affiliate['firstname']) . "', `lastname` = '" . $this->db->escape($affiliate['lastname']) . "', `email` = '" . $this->db->escape($affiliate['email']) . "', `telephone` = '" . $this->db->escape($affiliate['telephone']) . "', `password` = '" . $this->db->escape($affiliate['password']) . "', `cart` = '" . $this->db->escape(json_encode([])) . "', `wishlist` = '" . $this->db->escape(json_encode([])) . "', `newsletter` = '0', `custom_field` = '" . $this->db->escape(json_encode([])) . "', `ip` = '" . $this->db->escape($affiliate['ip']) . "', `status` = '" . $this->db->escape($affiliate['status']) . "', `date_added` = '" . $this->db->escape($affiliate['date_added']) . "'");

					$customer_id = $this->db->getLastId();
					
					$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($affiliate['firstname']) . "', lastname = '" . $this->db->escape($affiliate['lastname']) . "', company = '" . $this->db->escape($affiliate['company']) . "', address_1 = '" . $this->db->escape($affiliate['address_1']) . "', address_2 = '" . $this->db->escape($affiliate['address_2']) . "', city = '" . $this->db->escape($affiliate['city']) . "', postcode = '" . $this->db->escape($affiliate['postcode']) . "', zone_id = '" . (int)$affiliate['zone_id'] . "', country_id = '" . (int)$affiliate['country_id'] . "', custom_field = '" . $this->db->escape(json_encode([])) . "'");
			
					$address_id = $this->db->getLastId();
			
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
				} else {
					$customer_id = $customer_query->row['customer_id'];
				}
				
				$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `customer_id` = '" . (int)$customer_id . "'");
				
				if (!$customer_query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_affiliate` SET `customer_id` = '" . (int)$customer_id . "', `company` = '" . $this->db->escape($affiliate['company']) . "', `tracking` = '" . $this->db->escape($affiliate['code']) . "', `commission` = '" . (float)$affiliate['commission'] . "', `tax` = '" . $this->db->escape($affiliate['tax']) . "', `payment` = '" . $this->db->escape($affiliate['payment']) . "', `cheque` = '" . $this->db->escape($affiliate['cheque']) . "', `paypal` = '" . $this->db->escape($affiliate['paypal']) . "', `bank_name` = '" . $this->db->escape($affiliate['bank_name']) . "', `bank_branch_number` = '" . $this->db->escape($affiliate['bank_branch_number']) . "', `bank_account_name` = '" . $this->db->escape($affiliate['bank_account_name']) . "', `bank_account_number` = '" . $this->db->escape($affiliate['bank_account_number']) . "', `status` = '" . (int)$affiliate['status'] . "', `date_added` = '" . $this->db->escape($affiliate['date_added']) . "'");
				}

				$customer_query = $this->db->query("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "customer_affiliate' AND COLUMN_NAME = 'company'");
		
				if ($customer_query->num_rows && $customer_query->row['CHARACTER_MAXIMUM_LENGTH'] && $customer_query->row['CHARACTER_MAXIMUM_LENGTH'] < 60) {
					$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_affiliate` MODIFY COLUMN `company` VARCHAR(60)");
				}

				$affiliate_transaction_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate_transaction` WHERE `affiliate_id` = '" . (int)$affiliate['affiliate_id'] . "'");
			
				foreach ($affiliate_transaction_query->rows as $affiliate_transaction) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$affiliate_transaction['order_id'] . "', description = '" . $this->db->escape($affiliate_transaction['description']) . "', amount = '" . (float)$affiliate_transaction['amount'] . "', `date_added` = '" . $this->db->escape($affiliate_transaction['date_added']) . "'");
					
					$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_transaction_id = '" . (int)$affiliate_transaction['affiliate_transaction_id'] . "'");
				}
				
				$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `affiliate_id` = '" . (int)$customer_id . "' WHERE affiliate_id = '" . (int)$affiliate['affiliate_id'] . "'");
			}

			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate`");

			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_activity'");

			if ($affiliate_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_activity`");
			}

			$affiliate_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "affiliate_login'");

			if ($affiliate_query->num_rows) {
				$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_login`");
			}

			$this->db->query("DROP TABLE `" . DB_PREFIX . "affiliate_transaction`");
		}

		$api_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "api' AND COLUMN_NAME = 'name'");

		// API
		if ($api_query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` DROP COLUMN `username`");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` CHANGE COLUMN `name` `username` VARCHAR(64) NOT NULL");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "api` MODIFY COLUMN `username` VARCHAR(64) NOT NULL AFTER `api_id`");
		}

		// Events
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'sort_order'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` ADD `sort_order` INT(3) NOT NULL AFTER `action`");
		}

		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "event' AND COLUMN_NAME = 'date_added'");
		
		if ($query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "event` DROP COLUMN `date_added`");
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/account/customer/addAffiliate/after') . "' WHERE `code` = '" . $this->db->escape('activity_affiliate_add') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/account/customer/editAffiliate/after') . "' WHERE `code` = '" . $this->db->escape('activity_affiliate_edit') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/checkout/order/addOrderHistory/before') . "' WHERE `code` = '" . $this->db->escape('activity_order_add') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/checkout/order/addOrderHistory/after') . "' WHERE `code` = '" . $this->db->escape('mail_voucher') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/checkout/order/addOrderHistory/before') . "' WHERE `code` = '" . $this->db->escape('mail_order_add') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/checkout/order/addOrderHistory/before') . "' WHERE `code` = '" . $this->db->escape('mail_order_alert') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('catalog/model/checkout/order/addOrderHistory/before') . "' WHERE `code` = '" . $this->db->escape('statistics_order_history') . "'");		
		$this->db->query("UPDATE `" . DB_PREFIX . "event` SET `trigger` = '" . $this->db->escape('admin/model/sale/return/addOrderHistory/after') . "' WHERE `code` = '" . $this->db->escape('admin_mail_return') . "'");

		$query = $this->db->query("SELECT `event_id` FROM `" . DB_PREFIX . "event` WHERE `code` = 'mail_review'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "event` SET `code` = 'mail_review', `trigger` = 'catalog/model/catalog/review/addReview/after', `action` = 'mail/review', `status` = '1', `sort_order` = '0'");
		}

		// Country
		$this->db->query("UPDATE `" . DB_PREFIX . "country` SET `name` = 'România' WHERE `name` = 'Romania'");

		// Statistics
		$query = $this->db->query("SELECT `statistics_id` FROM `" . DB_PREFIX . "statistics` WHERE `code` = 'order_sale'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "statistics` (`statistics_id`, `code`, `value`) VALUES
				(1, 'order_sale', 0),
				(2, 'order_processing', 0),
				(3, 'order_complete', 0),
				(4, 'order_other', 0),
				(5, 'return', 0),
				(6, 'product', 0),
				(7, 'review', 0);
			");
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "statistics` SET `code` = '" . $this->db->escape('return') . "' WHERE `code` = '" . $this->db->escape('returns') . "'");

		// Zone
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Bacău') . "' WHERE `name` = '" . $this->db->escape('Bacau') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Bistrița-Năsăud') . "' WHERE `name` = '" . $this->db->escape('Bistrita-Nasaud') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Botoșani') . "' WHERE `name` = '" . $this->db->escape('Botosani') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Brașov') . "' WHERE `name` = '" . $this->db->escape('Brasov') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Brăila') . "' WHERE `name` = '" . $this->db->escape('Braila') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('București') . "' WHERE `name` = '" . $this->db->escape('Bucuresti') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Buzău') . "' WHERE `name` = '" . $this->db->escape('Buzau') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Caraș-Severin') . "' WHERE `name` = '" . $this->db->escape('Caras-Severin') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Călărași') . "' WHERE `name` = '" . $this->db->escape('Calarasi') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Constanța') . "' WHERE `name` = '" . $this->db->escape('Constanta') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Dâmbovița') . "' WHERE `name` = '" . $this->db->escape('Dimbovita') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Galați') . "' WHERE `name` = '" . $this->db->escape('Galati') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Ialomița') . "' WHERE `name` = '" . $this->db->escape('Ialomita') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Iași') . "' WHERE `name` = '" . $this->db->escape('Iasi') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Maramureș') . "' WHERE `name` = '" . $this->db->escape('Maramures') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Mehedinți') . "' WHERE `name` = '" . $this->db->escape('Mehedinti') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Mureș') . "' WHERE `name` = '" . $this->db->escape('Mures') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Neamț') . "' WHERE `name` = '" . $this->db->escape('Neamt') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Sălaj') . "' WHERE `name` = '" . $this->db->escape('Salaj') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Timiș') . "' WHERE `name` = '" . $this->db->escape('Timis') . "'");
		$this->db->query("UPDATE `" . DB_PREFIX . "zone` SET `name` = '" . $this->db->escape('Vâlcea') . "' WHERE `name` = '" . $this->db->escape('Valcea') . "'");

		// Customer IP
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "customer_ip' AND COLUMN_NAME = 'customer_ip_id'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_ip` ADD `store_id` INT(11) NOT NULL AFTER `customer_id`");
		}

		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "customer_ip' AND COLUMN_NAME = 'customer_ip_id'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_ip` ADD `country` VARCHAR(2) NOT NULL AFTER `ip`");
		}

		// Timezone
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_timezone'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `code` = 'config', `key` = 'config_timezone', `value` = 'UTC', `serialized` = '0'");
		}

		// Theme
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `value` = 'theme_default'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = 'default' WHERE `value` = 'theme_default'");
		}

		$query = $this->db->query("SELECT `extension_id` FROM `" . DB_PREFIX . "extension` WHERE `code` = 'theme_default'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "extension` SET `code` = 'default' WHERE `code` = 'theme_default'");
		}

		// Settings - Coupon
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'coupon'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_coupon' AND `key` = 'total_coupon_sort_order' WHERE `key` = 'coupon_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_coupon' AND `key` = 'total_coupon_status' WHERE `key` = 'coupon_status'");
		}

		// Settings - Credit
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'credit'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_credit' AND `key` = 'total_credit_sort_order' WHERE `key` = 'credit_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_credit' AND `key` = 'total_credit_status' WHERE `key` = 'credit_status'");
		}

		// Settings - Reward
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'reward'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_reward' AND `key` = 'total_reward_sort_order' WHERE `key` = 'reward_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_reward' AND `key` = 'total_reward_status' WHERE `key` = 'reward_status'");
		}

		// Settings - Shipping
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'shipping'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_shipping' AND `key` = 'total_shipping_sort_order' WHERE `key` = 'shipping_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_shipping' AND `key` = 'total_shipping_status' WHERE `key` = 'shipping_status'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_shipping' AND `key` = 'total_shipping_estimator' WHERE `key` = 'shipping_estimator'");
		}

		// Settings - SubTotal
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'sub_total'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_sub_total' AND `key` = 'total_sub_total_sort_order' WHERE `key` = 'sub_total_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_sub_total' AND `key` = 'total_sub_total_status' WHERE `key` = 'sub_total_status'");
		}

		// Settings - Tax
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'tax'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_tax' AND `key` = 'total_tax_sort_order' WHERE `key` = 'tax_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_tax' AND `key` = 'total_tax_status' WHERE `key` = 'tax_status'");
		}

		// Settings - Total
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'total'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_total' AND `key` = 'total_total_sort_order' WHERE `key` = 'total_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_total' AND `key` = 'total_total_status' WHERE `key` = 'total_status'");
		}

		// Settings - Voucher
		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'voucher'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_voucher' AND `key` = 'total_voucher_sort_order' WHERE `key` = 'voucher_sort_order'");
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `code` = 'total_voucher' AND `key` = 'total_voucher_status' WHERE `key` = 'voucher_status'");
		}

		// Report - Marketing
		$query = $this->db->query("SELECT `extension_id` FROM `" . DB_PREFIX . "extension` WHERE `type` = 'report' AND `code` = 'marketing'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'report', `code` = 'marketing'");
		}

		$query = $this->db->query("SELECT `setting_id` FROM `" . DB_PREFIX . "setting` WHERE `code` = 'report_customer_transaction' AND `key` = 'report_customer_transaction_status_sort_order'");

		if ($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `key` = 'report_customer_transaction_sort_order' WHERE `key` = 'report_customer_transaction_status_sort_order'");
		}

		// OPENCART_SERVER
		$upgrade = true;

		$file = DIR_OPENCART . 'admin/config.php';

		$lines = file(DIR_OPENCART . 'admin/config.php');

		foreach ($lines as $line) {
			if (strpos(strtoupper($line), 'OPENCART_SERVER') !== false) {
				$upgrade = false;

				break;
			}
		}

		if ($upgrade) {
			$output = '';

			foreach ($lines as $line_id => $line) {
				if (strpos($line, 'DB_PREFIX') !== false) {
					$output .= $line . "\n\n";
					$output .= 'define(\'OPENCART_SERVER\', \'https://www.opencart.com/\');' . "\n";
				} else {
					$output .= $line;
				}
			}

			$handle = fopen($file, 'w');

			fwrite($handle, $output);

			fclose($handle);
		}

		$files = glob(DIR_OPENCART . '{config.php,admin/config.php}', GLOB_BRACE);

		foreach ($files as $file) {
			$lines = file($file);
	
			for ($i = 0; $i < count($lines); $i++) { 
				if ((strpos($lines[$i], 'DIR_IMAGE') !== false) && (strpos($lines[$i + 1], 'DIR_STORAGE') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_STORAGE\', DIR_SYSTEM . \'storage/\');'));
				}

				if ((strpos($lines[$i], 'DIR_MODIFICATION') !== false) && (strpos($lines[$i + 1], 'DIR_SESSION') === false)) {
					array_splice($lines, $i + 1, 0, array('define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');'));
				}

				if (strpos($lines[$i], 'DIR_CACHE') !== false) {
					$lines[$i] = 'define(\'DIR_CACHE\', DIR_STORAGE . \'cache/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_DOWNLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_DOWNLOAD\', DIR_STORAGE . \'download/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_LOGS') !== false) {
					$lines[$i] = 'define(\'DIR_LOGS\', DIR_STORAGE . \'logs/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_MODIFICATION') !== false) {
					$lines[$i] = 'define(\'DIR_MODIFICATION\', DIR_STORAGE . \'modification/\');' . "\n";
				}

				if (strpos($lines[$i], 'DIR_SESSION') !== false) {
					$lines[$i] = 'define(\'DIR_SESSION\', DIR_STORAGE . \'session/\');' . "\n";
				}				

				if (strpos($lines[$i], 'DIR_UPLOAD') !== false) {
					$lines[$i] = 'define(\'DIR_UPLOAD\', DIR_STORAGE . \'upload/\');' . "\n";
				}
			}

			$output = implode('', $lines);
			
			$handle = fopen($file, 'w');

			fwrite($handle, $output);

			fclose($handle);
		}
	}
}