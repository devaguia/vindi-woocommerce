
<?php

class VindiRoutes
{

  /**
   * @var VindiSettings
   */
  private $settings;

  /**
   * @var void
   */
  private $api;

  function __construct()
  {

    $this->settings = new VindiSettings();
    $this->api = $this->settings->api;
  }

  /**
   * Post method for creating plan in the Vindi
   *
   * @since 1.0.0
   * @version 1.0.0
   * @return array
   */
  public function createPlan($data)
  {

    $response = $this->api->request('plans', 'POST', $data);

    return $response;
  }

  /**
   * Post method for creating product in the Vindi
   *
   * @since 1.0.0
   * @version 1.0.0
   * @return array
   */
  public function createProduct($data)
  {

    $response = $this->api->request('products', 'POST', $data);
    return $response;
  }

  /**
   * Post method for creating customer in the Vindi
   *
   * @since 1.0.0
   * @version 1.0.0
   * @return array
   */
  public function createCustomer($data)
  {

    $response = $this->api->request('customers', 'POST', $data);
    return $response;
  }

  /**
   * Update method for update profile customer in the Vindi
   *
   * @since 1.0.0
   * @version 1.0.0
   * @return array
   */
  public function updateCustomer($user_id, $data)
  {

    $response = $this->api->request(sprintf(
      'customers/%s',
      $user_id
    ), 'PUT', $data);

    return $response;
  }


  /**
   * Check if exists user in Vindi
   *
   * @since 1.0.0
   * @version 1.0.0
   * @return array
   */
  public function findCustomerByid($id)
  {

    $response = $this->api->request(sprintf(
      'customers/%s',
      $id
    ), 'GET');

    $userExists = isset($response['customers'][0]['id']);

    return $userExists;
  }
}
?>
