<?php
class ModelMarketingAffiliate extends Model {
	public function getAffiliate($customer_id) {
		$query = $this->db->query("SELECT DISTINCT *, CONCAT(c.`firstname`, ' ', c.`lastname`) AS `customer`, ca.`custom_field` FROM `" . DB_PREFIX . "customer_affiliate` ca LEFT JOIN `" . DB_PREFIX . "customer` c ON (ca.`customer_id` = c.`customer_id`) WHERE ca.`customer_id` = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function getAffiliateByTracking($tracking) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_affiliate` WHERE `tracking` = '" . $this->db->escape($tracking) . "'");

		return $query->row;
	}

	public function getAffiliates($data = []) {
		$sql = "SELECT *, CONCAT(c.`firstname`, ' ', c.`lastname`) AS `name`, ca.`status` FROM `" . DB_PREFIX . "customer_affiliate` ca LEFT JOIN `" . DB_PREFIX . "customer` c ON (ca.`customer_id` = c.`customer_id`)";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.`firstname`, ' ', c.`lastname`) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_tracking'])) {
			$implode[] = "ca.`tracking` = '" . $this->db->escape($data['filter_tracking']) . "'";
		}

		if (!empty($data['filter_commission'])) {
			$implode[] = "ca.`commission` = '" . $data['filter_commission'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "ca.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(ca.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'name',
			'ca.tracking',
			'ca.commission',
			'ca.status',
			'ca.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalAffiliates($data = array()) {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_affiliate` ca LEFT JOIN `" . DB_PREFIX . "customer` c ON (ca.`customer_id` = c.`customer_id`)";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.`firstname`, ' ', c.`lastname`) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_tracking'])) {
			$implode[] = "ca.`tracking` = '" . $this->db->escape($data['filter_tracking']) . "'";
		}

		if (!empty($data['filter_commission'])) {
			$implode[] = "ca.`commission` = '" . $data['filter_commission'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$implode[] = "ca.`status` = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(ca.`date_added`) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}