<?php

namespace WPLF;

function isDebug() {
  return defined('WP_DEBUG') && WP_DEBUG == true; // Loose comparison to support 1, 'yes' etc
}

function isRest() {
  return defined('REST_REQUEST');
}

function log($anything) {
  error_log('WPLF: ' . print_r($anything, true));
}

function minifyHtml(string $html) {
  return str_replace(array("\n", "\r"), ' ', $html);
}

/**
 * Strip <form> tags from the form content, you can't have forms inside forms
 */
function stripFormTags($content) {
  return preg_replace('/<\/?form.*>/i', '', $content);
}

function parseEmailToField(string $value) {
  $result = '';

  // If the field contains commas, assume it's a well-formed list of email addresses.
  if (strpos($value, ',') > 0) {
    $emails = explode(',', $value);

    foreach ($emails as $email) {
      $email = trim($email);
      $email = sanitize_email($email) . ', ';
      $to .= $email;
    }

    $result = rtrim($to, ', ');
  } else {
    $result = sanitize_email($value);
  }

  return $result;
}

function findFieldByName(string $name, array $fields) {
  foreach ($fields as $field) {
    if ($field['name'] === $name) {
      return $field;
    }
  }

  return false;
}

function isFileArray(array $data) {
  $keys = ['name', 'type', 'tmp_name', 'error', 'size'];

  foreach ($keys as $k => $v) {
    if (!in_array($k, $data)) {
      return false;
    }
  }

  return true;
}

function getFileUploadError(int $errorNumber) {
  $errors = [
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
  ];

  if ($errorNumber === 0) {
    return false;
  }

  return new Error($errors[$errorNumber]);
}

function stringifyFieldValue($value, string $type) {
  switch ($type) {
    case 'file': {
      // potential micro-optimization here
      $wpDir = get_home_path();
      $wpUrl = get_home_url(null, '/');

      $filepaths = explode(', ', $value);
      $fileurls = array_map(function($path) use ($wpDir, $wpUrl) {

        return str_replace($wpDir, $wpUrl, $path);
      }, $filepaths);

      return join(', ', $fileurls);
      // return $value;
      break;
    }

    default: {
      return $value;
    }
  }

}

function getUploadedFiles(): ?array {
  $uploads = $_FILES;

  return !empty($uploads) ? $uploads : null;
}

function db() {
  global $wpdb;

  return [
    $wpdb,
    $wpdb->prefix,
  ];
}

function currentUrl() {
  $protocol = (isset($_SERVER['HTTPS']) ? "https" : "http");

  return "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
