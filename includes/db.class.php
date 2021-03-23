<?php

  class DBAPI
  {

    static function get_db_conn()
    {
      return new SQLite3("../../data/f20ec-store.db");
    }

    static function get_product_images($conn, $id)
    {
      $q = $conn->prepare(" SELECT prod_img_link
                            FROM product_img
                            WHERE product_img.prod_id = :id");
      $q->bindValue(":id", $id);
      $result = $q->execute();

      $r_array = array();
      while($row = $result->fetchArray())
      {
        array_push($r_array, $row);
      }
      return $r_array;
    }

    static function get_product($conn, $id)
    {
      $q = $conn->prepare(" SELECT * 
                            FROM products
                            WHERE products.prod_id = :id");
      $q->bindValue(":id", $id);
      
      return $q->execute()->fetchArray();
    }

    static function get_all_products($conn, $page)
    {
      $q = $conn->prepare(" SELECT *
                            FROM products
                            LIMIT 20 OFFSET :off");
      $q->bindValue(":off", ($page-1)*20);
      $result = $q->execute();

      $r_array = array();
      while($row = $result->fetchArray())
      {
        array_push($r_array, $row);
      }
      return $r_array;
    }

    static function check_user_exists($conn, $user)
    {
      $q = $conn->prepare(" SELECT acc_username 
                            FROM accounts
                            WHERE acc_username = :user");
      $q->bindValue(":user", $user);

      if($q->execute()->fetchArray() != null) return true;
      return false;
    }

    static function verify_password($conn, $user, $password)  {
      $q = $conn->prepare(" SELECT acc_password 
                            FROM accounts
                            WHERE acc_username = :user");
      $q->bindValue(":user", $user);

      try
      {
        $real_password = $q->execute()->fetchArray()["acc_password"];
        if(password_verify($password, $real_password)) return true;
        return false;
      }
      catch(Exception $e)
      {
        return false;
      }
    }

    static function create_account($conn, $user, $pass) {
      $q = $conn->prepare(" INSERT INTO accounts 
                              (acc_username, acc_password, acc_created)
                            VALUES
                              (:user, :pass, strftime('%s','now'))");
      $q->bindValue(":user", $user);
      $q->bindValue(":pass", password_hash($pass, PASSWORD_BCRYPT));
      $q->execute();

      return self::check_user_exists($conn, $user);
    }

    static function sanitize_input($i)  {
      /*
        Sanitize input
      */
  
      $i = strip_tags($i);        // Remove dangerous tags
      $i = htmlspecialchars($i);  // Escape any remaining special characters
  
      return $i;
    }

  }

?>