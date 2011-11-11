<?php
/**
 * Registrar Example File
 * 
 * Be sure to rename this file 'class.registrar_registrarname.php', where
 * 'registrarname' is the name of the class you've created. Please send feedback regarding
 * the ease of use (or lack thereof!) of this file to <support@ubersmith.com>.
 *
 * Before you can use your new registrar, you'll need to add an entry to the 'domain_registrars'
 * with at least the registrar name. You can configure the rest through the Ubersmith UI.
 *
 * @author John Smith <mstyne@ubersmith.com>
 * @version $Id$
 * @package ubersmith
 * @subpackage default
 **/

/**
 * Registrar Example
 */

/**
 * Registrar Example Class
 * 
 * Change out occurrences of 'example' in this class to whatever registrar you're implementing support for.
 * For example, Ubersmith uses the names'opensrs', 'enom', 'nominet', etc. 
 *
 * @package ubersmith
 * @author John Smith
 */
class registrar_example extends registrar
{
	var $input                  = array();
	var $config                 = array();
	var $info                   = array();
	var $result                 = '';
	var $order_id               = '';
	
	/**
	 * Registrar Function
	 * 
	 * Notes:
	 * - This Function handles the configuration of the registrar and should not be modified
	 * except to change the function name from 'registrar_example' to 'registrar_registrarname' where
	 * 'registrarname' is the name of the class you've created.
	 *
	 * @param string $request 
	 * @return bool
	 * @author John Smith
	 */
	function registrar_example($request)
	{
		$this->input = $request;
		
		$query = 'SELECT * FROM domain_registrars WHERE registrar=?';
		$result = $_SESSION['DB']->query($query,array($this->input['registrar']));
		if (DB::isError($result))
		{
			$_SESSION['ERROR']->set(__FUNCTION__,uber_i18n('select failed.$query'));
			trigger_error('sql query failed');
			return false;
		}
		
		$this->config = $result->fetchRow();
		if (!is_array($this->config) || empty($this->config))
		{
			$_SESSION['ERROR']->set(__FUNCTION__,uber_i18n('select config failed'));
			return false;
		}
	}
	
	/**
	 * Return Order ID
	 * 
	 * Notes:
	 * - No need to modify this function.
	 *
	 * @return string
	 * @author John Smith
	 */
	function get_order_id()
	{
		return $this->order_id;
	}
	
	/**
	 * Register Domain Function
	 * 
	 * Returns: 
	 * - Success: Boolean 'true'
	 * - Failure: PEAR error object
	 * 
	 * Notes: 
	 * - The order ID (or other unique identifier) returned by the registrar must be stored as $this->order_id.
	 *
	 * @return mixed
	 * @author John Smith
	 */
	function register_domain()
	{
		
		/**
		 * Contact data in $this->input uses the prefixes: admin_, billing_, owner_, technical_ ...
		 * 
		 * fname    - First Name
		 * lname    - Last Name
		 * company  - Company Name
		 * address1 - Address Line 1
		 * address2 - Address Line 2
		 * city     - City
		 * state    - State
		 * zip      - Zip
		 * country  - Country
		 * phone    - Phone
		 * fax      - Fax
		 * email    - Email Address
		 * 
		 * Example:
		 * 
		 * $this->input['admin_fname']
		 * $this->input['admin_lname']
		 * $this->input['admin_company']
		 * $this->input['admin_address1']
		 * $this->input['admin_address2']
		 * etc.
		 * 
		 * Other values included are:
		 * 
		 * username   - Client's Reseller Username
		 * password   - Client's Reseller Password
		 * autorenew  - Auto Renew (0 or 1)
		 * name       - Domain SLD
		 * tld        - Domain TLD
		 * privacy    - Domain Privacy (0 or 1)
		 * lockdomain - Lock Domain (0 or 1)
		 * years      - Registration in Years
		 * 
		 * Configuration data in $this->config is as follows:
		 * 
		 * ns_type      - Name Server Type (0 or 1)
		 * primary_ns   - Primary Nameserver
		 * secondary_ns - Secondary Nameserver
		 * 
		 */

		$request = array(
			// Build array of data to be submitted to domain reseller
		);
		
		$result = $this->registrar_post($request);
		
		if (PEAR::isError($result)) {
			return $result;
		}
		
		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain registration failed.',1);
		}
		
		/**
		 * Set $this->order_id to order ID or other unique value returned from registrar
		 */
		$this->order_id = $result['order_id'];
		
		return true;
	}
	
	/**
	 * Domain Renew Function
	 * 
	 * Returns: 
	 * - Success: Array containing 'expires' and 'orderid'
	 * - Failure: PEAR error object
	 * 
	 * Notes: 
	 * - The order ID (or other unique identifier) returned by the registrar must be stored as $this->order_id.
	 *
	 * @return mixed
	 * @author John Smith
	 */
	function renew_domain()
	{
		/**
		 * Same data provided as used in register_domain function
		 */
		$request = array(
			// Build array of data to be submitted to domain reseller
		);
		
		$result = $this->registrar_post($request);
		
		if (PEAR::isError($result)) {
			return $result;
		}

		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain registration failed.',1);
		}
		
		/**
		 * Set $this->order_id to order ID or other unique value returned from registrar
		 */
		$this->order_id = $result['order_id'];
		
		/**
		 * 'expires' is the domain's new expiration date in a UNIX timestamp format
		 * 'orderid' is the order ID or other unique value returned from registrar
		 */
		$update = array(
			'expires'  => $expiration_date,
			'orderid'  => $result['attributes']['order_id'],
		);
		
		return $update;
		
		
	}
	
	/**
	 * Domain Reactivation Function
	 *
	 * Notes:
	 * - If registrar requires reactivation as a separate call, implement that call here.
	 * 
	 * @return mixed
	 * @author John Smith
	 */
	function reactivate_domain()
	{
		/**
		 * Same data provided as used in register_domain function
		 */
		return PEAR::raiseError(uber_i18n('Reactivation not required for this registrar, please renew domain instead'),1);
	}
	
	
	/**
	 * Domain Lookup Function
	 * 
	 * Returns
	 *  - Array with sld, tld, expiration, registered, autorenew, registrahold
	 * 
	 * Notes:
	 * - This function queries the registrar for details regarding the domain.
	 * 
	 * @return void
	 * @author John Smith
	 */
	function domain_details()
	{
		/**
		 * Provides data $this->input['sld'] and $this->input['tld']
		 * 
		 * 'sld' - Second Level Domain (domain name)
		 * 'tld' - Top Level Domain (.com, .net, .org, etc.)
		 */
		
		$request = array(
			// Build array of data to be submitted to domain reseller
		);
		
		$result = $this->registrar_post($request);
		
		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain registration failed.',1);
		}
		
		/**
		 * This function should return this data as provided by the registrar:
		 * 
		 * 'expiration'    - Domain expiration date as UNIX timestamp
		 * 'registered'    - Date domain was registered as UNIX timestamp
		 * 'autorenew'     - Autorenew configured (0 or 1)
		 * 'registrarhold' - Domain hold enabled (0 or 1)
		 */
		$details = array(
			'sld'           => $this->input['sld'],
			'tld'           => $this->input['tld'],
			'expiration'    => $expiration,
			'registered'    => $registered,
			'autorenew'     => $autorenew,
			'registrarhold' => $registrarhold,
		);
		
		return $details;
	}
	
	/**
	 * Domain Transfer Function
	 * 
	 * Returns: 
	 * - Success: bool true
	 * - Failure: PEAR error object
	 * 
	 * Notes: 
	 * - The order ID (or other unique identifier) returned by the registrar must be stored as $this->order_id.
	 * 
	 * @return mixed
	 * @author John Smith
	 */
	function transfer_domain()
	{
		/**
		 * Same data provided as used in register_domain function
		 */
		
		$request = array(
			// Build array of data to be submitted to domain reseller
		);
		
		$result = $this->registrar_post($request);
		
		if (PEAR::isError($result)) {
			return $result;
		}
		
		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain transfer failed.',1);
		}
		
		/**
		 * Set $this->order_id to order ID or other unique value returned from registrar
		 */
		$this->order_id = $result['order_id'];
		
		return true;
	}
	
	/**
	 * 
	 * Returns:
	 * 
	 * - Success: string 'availble' or 'not available'
	 * - Failure: PEAR error object
	 * 
	 * Notes:
	 * - This function determines if the domain is available at the registrar.
	 *
	 * @return string
	 * @author John Smith
	 */
	function reg_lookup_domain()
	{
		/**
		 * Provides data $this->input['sld'] and $this->input['tld']
		 * 
		 * 'sld' - Second Level Domain (domain name)
		 * 'tld' - Top Level Domain (.com, .net, .org, etc.)
		 */
		
		$request = array(
			// Build array of data to be submitted to domain reseller
		);
		
		$result = $this->registrar_post($request);
		
		if (PEAR::isError($result)) {
			return $result;
		}
		
		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain lookup failed.',1);
		}
		
		switch ($result['response_text']) {
			case 'Domain taken':
				return 'not available';
				break;
			default:
			case 'Domain available':
				return 'available';
				break;
		}
		
		return PEAR::raiseError('lookup domain failed.',1);
	}
	
	/**
	 * Registrar Communication Function
	 * 
	 * Returns:
	 * - Success: Array with result of registrar query
	 * - Failure: PEAR Error object
	 *
	 * @param string $request 
	 * @return void
	 * @author Michael Styne
	 */
	function registrar_post($request)
	{
		$this->registrar_log(http_build_query($request),'registrar request');
		
		/**
		 * Your communication with the registrar should occur here, and the
		 * response stored in the $result variable.
		 */
		$result = array();
		
		if (!is_array($result)) {
			return PEAR::raiseError('Unexpected response from registrar',1);
		}
		
		if ($result === NULL) {
			return PEAR::raiseError('No response from domain registrar',2);
		}

		/**
		 * If something unexpected happens, return a PEAR Error object:
		 */
		if ($failure) {
			return PEAR::raiseError('Domain transfer failed.',1);
		}

		$this->registrar_log(http_build_query($result),'registrar response');
		
		return $result;
	}

	/**
	 * Available Registration terms for domain
	 * 
	 * Returns:
	 * - Array
	 * 
	 * Notes:
	 *  - This function returns specialized terms for specific TLDs.
	 *  - Included is an example switch statement for 'uk' and 'tv' domains.
	 *
	 * @return array
	 * @author John Smith
	 */
	function available_terms()
	{
		// switch ($this->input['tld']) {
		// 	case 'uk':
		// 		return array(
		// 			2 => '2 Years',
		// 		);
		// 		break;
		// 	case 'tv':
		// 		return array(
		// 			1   => '1 Year',
		// 			2   => '2 Years',
		// 			3   => '3 Years',
		// 			5   => '5 Years',
		// 			10  => '10 Years',
		// 		);
		// 		break;
		// 	default:
		// 		return array(
		// 			1  => '1 Year',
		// 			2  => '2 Years',
		// 			3  => '3 Years',
		// 			4  => '4 Years',
		// 			5  => '5 Years',
		// 			6  => '6 Years',
		// 			7  => '7 Years',
		// 			8  => '8 Years',
		// 			9  => '9 Years',
		// 			10 => '10 Years',
		// 		);
		// 		break;
		// }
		
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
	
	/**
	 * Retrieve External Attributes
	 * 
	 * Returns:
	 * - Array
	 * 
	 * Notes:
	 * - If a TLD has special attributes, you can retrieve them from the registrar (if available)
	 * or define them as an array here. Included is an example for .au as used by OpenSRS.
	 * 
	 *
	 * @return void
	 * @author John Smith <mike@ubersmith.com>
	 */
	function get_external_attributes()
	{
		// switch($this->input['tld']) {
		// 	case 'au':
		// 		return array(
		// 			'0' => array(
		// 				'type'        => 'text',
		// 				'name'        => 'eligibility_id',
		// 				'description' => 'Eligibility Document ID',
		// 			),
		// 			'1' => array(
		// 				'type'       => 'select',
		// 				'name'       => 'eligibility_id_type',
		// 				'description'=> 'Eligibility ID Type',
		// 				'options'    => array(
		// 					'ACN'    => 'Australian Company Number',
		// 					'ABN'    => 'Australian Business Number',
		// 					'VIC BN' => 'Victoria Business Number',
		// 					'NSW BN' => 'New South Wales Business Number',
		// 					'SA BN'  => 'South Australia Business Number',
		// 					'NT BN'  => 'Northern Territory Business Number',
		// 					'WA BN'  => 'Western Australia Business Number',
		// 					'TAS BN' => 'Tasmania Business Number',
		// 					'ACT BN' => 'Australian Capital Territory Business Number',
		// 					'QLD BN' => 'Queensland Business Number',
		// 					'TM'     => 'TM',
		// 					'OTHER'  => 'Other',
		// 				),
		// 			),
		// 			'2' => array(
		// 				'type'        => 'text',
		// 				'name'        => 'eligibility_name',
		// 				'description' => 'Eligibility ID Name',
		// 			),
		// 			'3' => array(
		// 				'type'       => 'select',
		// 				'name'       => 'eligibility_type',
		// 				'description'=> 'Eligibility Type',
		// 				'options'    => array(
		// 					'Charity', 
		// 					'Child Care Centre',
		// 					'Citizen/Resident',
		// 					'Club',
		// 					'Commercial Statutory Body',
		// 					'Company',
		// 					'Government School',
		// 					'Higher Education Institution',
		// 					'Incorporated Association',
		// 					'Industry Body',
		// 					'National Body',
		// 					'Non-Government School',
		// 					'Non-profit Organisation',
		// 					'Other',
		// 					'Partnership',
		// 					'Pending TM Owner',
		// 					'Political Party',
		// 					'Pre-school',
		// 					'Registered Business',
		// 					'Religious/Church Group',
		// 					'Research Organisation',
		// 					'Sole Trader',
		// 					'Trade Union',
		// 					'Trademark Owner',
		// 					'Training Organisation',
		// 				),
		// 			),
		// 			'4' => array(
		// 				'type'        => 'text',
		// 				'name'        => 'registrant_id',
		// 				'description' => 'Registrant ID',
		// 			),
		// 			'5' => array(
		// 				'type'       => 'select',
		// 				'name'       => 'registrant_id_type',
		// 				'description'=> 'Registrant ID Type',
		// 				'options'    => array(
		// 					'ACN'    => 'Australian Company Number',
		// 					'ABN'    => 'Australian Business Number',
		// 					'OTHER'  => 'Other',
		// 				),
		// 			),
		// 			'6' => array(
		// 				'type'        => 'text',
		// 				'name'        => 'registrant_name',
		// 				'description' => 'Registrant Name',
		// 			),
		// 		);
		// 		break;
		// 	default:
		// 		return array();
		// 		break;
		// }
		
		return array();
	}
	
	/**
	 * Get Available Services
	 * 
	 * Returns:
	 * - Array
	 * 
	 * Notes:
	 * - If the reseller provides special additional services, include them here.
	 * Currently, Ubersmith only supports 'privacy'.
	 *
	 * @return void
	 * @author John Smith
	 */
	function get_available_services()
	{
		return array(
			'privacy' => 'WHOIS Privacy Protection',
		);
	}
	
}

// end of script
