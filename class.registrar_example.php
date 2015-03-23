<?php

/**
 * Registrar Example File
 *
 * You will want to change registrar_example to something a little
 * more descriptive, however be sure to retain the registrar_ prefix.
 *
 * When complete, place your finished module into include/registrar/
 *
 * Before you can use your new registrar, you'll need to add an entry to the
 * 'domain_registrars' table with at least the registrar name. You can
 * configure the rest through the Ubersmith UI.
 *
 * @package ubersmith_customizations
 */

/**
 * Registrar Example Class
 *
 * @package ubersmith_customizations
 */
class registrar_example extends domain_registrar
{
	public $test_url = 'https://test.example.com/api/endpoint';
	public $live_url = 'https://live.example.com/api/endpoint';

	public static function name()
	{
		return 'Example';
	}

	/**
	 * Get information about a domain
	 *
	 * @return array  an associative array with the following keys:
	 *                + sld = second level domain
	 *                + tld = top level domain
	 *                + expiration = UNIX timestamp of expiration date
	 *                + registered = UNIX timestamp of registration date
	 *                + autorenew = 0|1
	 *                + registrahold = 0|1
	 */
	public function domain_details($request)
	{
		$result = $this->registrar_post($request);
		if ($result === false) {
			return false;
		}

		if (!isset($result['registry_expiredate'])
			|| !isset($result['registry_createdate'])
			|| !isset($result['auto_renew'])
		) {
			return $this->error(uber_i18n('invalid response from registrar'));
		}

		$details = array(
			'sld'        => $request['name'],
			'tld'        => $request['tld'],
			'expiration' => str2time($result['registry_expiredate']),
			'registered' => str2time($result['registry_createdate']),
			'autorenew'  => (int) $result['auto_renew'],
		);

		return $details;
	}

	/**
	 * Determine if the domain is available or not
	 *
	 * @return string|false  "available" or "not available" on success, false
	 *                       on error; use $obj->last_error() for info
	 */
	public function reg_lookup_domain($request)
	{
		$data = array(
			'action' => 'LOOKUP',
			'domain' => $request['name'] .'.'. $request['tld'],
		);

		$result = $this->registrar_post($data);
		if ($result === false) {
			return false;
		}

		switch ($result['response_text']) {
			case 'Domain taken':
				return 'not available';
			default:
			case 'Domain available':
				return 'available';
		}

		return $this->error(uber_i18n('lookup domain failed'));
	}

	/**
	 * Available Registration terms for domain
	 *
	 * Notes:
	 *  - This function returns specialized terms for specific TLDs.
	 *  - Included is an example switch statement for 'uk' and 'tv' domains.
	 *
	 * @return array
	 */
	public function available_terms($request = array())
	{
		switch ($request['tld']) {
			case 'uk':
				return array(
					2 => '2 Years',
				);
			case 'tv':
				return array(
					1   => '1 Year',
					2   => '2 Years',
					3   => '3 Years',
					5   => '5 Years',
					10  => '10 Years',
				);
			default:
				return array(
					1  => '1 Year',
					2  => '2 Years',
					3  => '3 Years',
					4  => '4 Years',
					5  => '5 Years',
					6  => '6 Years',
					7  => '7 Years',
					8  => '8 Years',
					9  => '9 Years',
					10 => '10 Years',
				);
		}
	}

	/**
	 * Retrieve External Attributes
	 *
	 * Notes:
	 * - If a TLD has special attributes, you can retrieve them from the
	 * registrar (if available) or define them as an array here. Included
	 * is an example for .au as used by OpenSRS.
	 *
	 * @return array
	 */
	public function get_external_attributes($request = array())
	{
		switch ($request['tld']) {
			case 'au':
				return array(
					'0' => array(
						'type'        => 'text',
						'name'        => 'eligibility_id',
						'description' => 'Eligibility Document ID',
					),
					'1' => array(
						'type'       => 'select',
						'name'       => 'eligibility_id_type',
						'description'=> 'Eligibility ID Type',
						'options'    => array(
							'ACN'    => 'Australian Company Number',
							'ABN'    => 'Australian Business Number',
							'VIC BN' => 'Victoria Business Number',
							'NSW BN' => 'New South Wales Business Number',
							'SA BN'  => 'South Australia Business Number',
							'NT BN'  => 'Northern Territory Business Number',
							'WA BN'  => 'Western Australia Business Number',
							'TAS BN' => 'Tasmania Business Number',
							'ACT BN' => 'Australian Capital Territory Business Number',
							'QLD BN' => 'Queensland Business Number',
							'TM'     => 'TM',
							'OTHER'  => 'Other',
						),
					),
					'2' => array(
						'type'        => 'text',
						'name'        => 'eligibility_name',
						'description' => 'Eligibility ID Name',
					),
					'3' => array(
						'type'       => 'select',
						'name'       => 'eligibility_type',
						'description'=> 'Eligibility Type',
						'options'    => array(
							'Charity',
							'Child Care Centre',
							'Citizen/Resident',
							'Club',
							'Commercial Statutory Body',
							'Company',
							'Government School',
							'Higher Education Institution',
							'Incorporated Association',
							'Industry Body',
							'National Body',
							'Non-Government School',
							'Non-profit Organisation',
							'Other',
							'Partnership',
							'Pending TM Owner',
							'Political Party',
							'Pre-school',
							'Registered Business',
							'Religious/Church Group',
							'Research Organisation',
							'Sole Trader',
							'Trade Union',
							'Trademark Owner',
							'Training Organisation',
						),
					),
					'4' => array(
						'type'        => 'text',
						'name'        => 'registrant_id',
						'description' => 'Registrant ID',
					),
					'5' => array(
						'type'       => 'select',
						'name'       => 'registrant_id_type',
						'description'=> 'Registrant ID Type',
						'options'    => array(
							'ACN'    => 'Australian Company Number',
							'ABN'    => 'Australian Business Number',
							'OTHER'  => 'Other',
						),
					),
					'6' => array(
						'type'        => 'text',
						'name'        => 'registrant_name',
						'description' => 'Registrant Name',
					),
				);
			default:
				return array();
		}
	}

	/**
	 * Get Available Services
	 *
	 * Notes:
	 * - If the reseller provides special additional services, include
	 * them here.
	 *
	 * Currently, Ubersmith only supports 'privacy'.
	 *
	 * @return array
	 */
	public function get_available_services()
	{
		return array(
			'privacy' => 'WHOIS Privacy Protection',
		);
	}

	public function get_nameservers($request)
	{
		// Add code here.
	}

	public function update_nameservers($request)
	{
		// Add code here.
	}

	public function get_contact_info($request)
	{
		$required_fields = array(
			'name',
			'tld',
		);

		foreach ($required_fields as $field) {
			if (empty($request[$field])) {
				return $this->error(uber_i18nf('No %s specified', $field));
			}
		}

		$data = array(
			'action' => 'GET',
			'domain' => $request['name'] .'.'. $request['tld'],
		);

		$result = $this->registrar_post($data);
		if ($result === false) {
			return false;
		}

		$details = array();

		if (isset($result['contact_set']) && is_array($result['contact_set'])) {
			foreach ($result['contact_set'] as $id => $group) {
				$details[$id] = array(
					$id .'_fname'    => $group['first_name'],
					$id .'_lname'    => $group['last_name'],
					$id .'_company'  => $group['org_name'],
					$id .'_email'    => $group['email'],
					$id .'_address1' => $group['address1'],
					$id .'_address2' => $group['address2'],
					$id .'_city'     => $group['city'],
					$id .'_state'    => $group['state'],
					$id .'_zip'      => $group['postal_code'],
					$id .'_country'  => $group['country'],
					$id .'_phone'    => $group['phone'],
					$id .'_fax'      => $group['fax'],
				);
			}
		}

		return $details;
	}

	/**
	 * Register a domain
	 *
	 * @return int|false  order id number on success, false on error;
	 *                    use $obj->last_error() for info
	 */
	protected function _register_domain($request)
	{
		$data = array(
			'action' => 'register',
			'domain' => $request['name'] .'.'. $request['tld'],
			'reg_username' => $request['username'],
			'reg_password' => $request['password'],
			'custom_nameservers' => $this->data['ns_type'],
			'period' => $request['years'],
			'admin' => array(
				'first_name' => $request['admin_fname'],
				'last_name' => $request['admin_lname'],
				'org_name' => $request['admin_company'],
				'address1' => $request['admin_address1'],
				'address2' => $request['admin_address2'],
				'city' => $request['admin_city'],
				'state' => $request['admin_state'],
				'postal_code' => $request['admin_zip'],
				'country' => $request['admin_country'],
				'phone' => $request['admin_phone'],
				'fax' => $request['admin_fax'],
				'email' => $request['admin_email'],
			),
			'billing' => array(
				'first_name' => $request['billing_fname'],
				'state' => $request['billing_state'],
				'country' => $request['billing_country'],
				'address1' => $request['billing_address1'],
				'address2' => $request['billing_address2'],
				'last_name' => $request['billing_lname'],
				'city' => $request['billing_city'],
				'fax' => $request['billing_fax'],
				'postal_code' => $request['billing_zip'],
				'email' => $request['billing_email'],
				'phone' => $request['billing_phone'],
				'org_name' => $request['billing_company'],
			),
			'owner' => array(
				'first_name' => $request['owner_fname'],
				'state' => $request['owner_state'],
				'country' => $request['owner_country'],
				'address1' => $request['owner_address1'],
				'address2' => $request['owner_address2'],
				'last_name' => $request['owner_lname'],
				'city' => $request['owner_city'],
				'fax' => $request['owner_fax'],
				'postal_code' => $request['owner_zip'],
				'email' => $request['owner_email'],
				'phone' => $request['owner_phone'],
				'org_name' => $request['owner_company'],
			),
			'tech' => array(
				'first_name' => $request['technical_fname'],
				'state' => $request['technical_state'],
				'country' => $request['technical_country'],
				'address1' => $request['technical_address1'],
				'address2' => $request['technical_address2'],
				'last_name' => $request['technical_lname'],
				'city' => $request['technical_city'],
				'fax' => $request['technical_fax'],
				'postal_code' => $request['technical_zip'],
				'email' => $request['technical_email'],
				'phone' => $request['technical_phone'],
				'org_name' => $request['technical_company'],
			),
		);

		$result = $this->registrar_post($data);
		if ($result === false) {
			return false;
		}

		if (!isset($result['order_id'])) {
			return $this->error(uber_i18n('invalid response from registrar'));
		}

		return $result['order_id'];
	}

	protected function _autorenew_domain($request)
	{
		// Add code here.
	}

	protected function _renew_domain($request)
	{
		// Add code here.
	}

	protected function _reactivate_domain($request)
	{
		// Add code here.
	}

	protected function _transfer_domain($request)
	{
		// Add code here.
	}

	/**
	 * Send the data to the registrar
	 *
	 * @param array $request  an associative array of keys and values
	 *
	 * @return array|false  array on success, false on error;
	 *                      use $obj->last_error() for info
	 */
	protected function registrar_post($request)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->get_url());
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

		$this->registrar_log(json_encode($request), 'command');

		$response = curl_exec($ch);
		if ($response === false) {
			$error = curl_error($ch);
			$this->registrar_log($error, 'curl error');
			return $this->error($error);
		}
		curl_close($ch);

		$result = json_decode($response,true);
		if ($result === false) {
			$error = json_last_error_msg();
			$this->registrar_log($error, 'json error');
			return $this->error($error);
		}

		$this->registrar_log($response, 'response');

		return $result;
	}
}

// end of script
