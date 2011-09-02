<?php
/**
 * Implements template_preprocess_page().
 */
function unl_digitalsignage_preprocess_page(&$vars) {
  drupal_add_js('if (typeof(UNL) == "undefined") {var UNL = (function(){return {};})();}', 'inline');
  drupal_add_js(drupal_get_path('theme', 'unl_digitalsignage') . '/scripts/unl_digitalsignage.js');
  drupal_add_js(drupal_get_path('theme', 'unl_digitalsignage') . '/scripts/unl_digitalsignage_unlalert.js');
  drupal_add_js(drupal_get_path('theme', 'unl_digitalsignage') . '/scripts/jquery.animate-colors.js'); //http://www.bitstorm.org/jquery/color-animation/
  drupal_add_js(drupal_get_path('theme', 'unl_digitalsignage') . '/scripts/jquery.animate-textshadow.js'); //http://plugins.jquery.com/project/animate-textshadow
  drupal_add_js(drupal_get_path('theme', 'unl_digitalsignage') . '/scripts/jquery.ThreeDots.js'); //http://tpgblog.com/2009/12/21/threedots-the-jquery-ellipsis-plugin/
}

/**
 * Implements template_preprocess_field().
 */
function unl_digitalsignage_preprocess_field(&$vars, $hook) {

  switch($vars['element']['#bundle']) {
    case 'digital_sign':
      switch($vars['element']['#field_name']) {
        case 'body':
          break;
        case 'field_newssources':
          _unl_digitalsignage_yqlfeed($vars['items'], 'field_newssources');
          break;
        case 'field_videosources':
          _unl_digitalsignage_yqlfeed($vars['items'], 'field_videosources');
          break;
        case 'field_twitter':
          _unl_digitalsignage_twitterfeed($vars['items'][0]['#markup'], 'field_twitter');
          break;
        case 'field_beautyshots':
          break;
      }
      break;
    case 'digital_sign_housing':
      break;
    case 'digital_sign_union':
      break;
    default:
  }

}

function _unl_digitalsignage_yqlfeed($items, $fieldname) {
  foreach ($items as $key => $item) {
    $feeds[] = "'".urlencode(html_entity_decode($item['#markup']))."'";
  }
  $yqlfeeds = implode('%2C',$feeds);
  // select * from rss where url in ('http://ucommrasmussen.unl.edu/workspace/drupal7/ascweb/news.xml','http://scarlet.unl.edu/?feed=rss2&tag=college-of-arts-and-sciences') | sort(field="pubDate",descending="true")
  $yql = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20rss%20where%20url%20in%20('.$yqlfeeds.')%20%7C%20sort(field%3D%22pubDate%22%2Cdescending%3D%22true%22)&format=json';
  return drupal_add_js('UNL.digitalSignage.feeds["'.$fieldname.'"] = "'.$yql.'"', 'inline');
}

function _unl_digitalsignage_twitterfeed($item, $fieldname) {
  global $base_path;
  if (strpos($item, '/') === false) {
    $twitterapi = 'https://api.twitter.com/1/statuses/user_timeline.json?screen_name='.$item.'&include_rts=true&count=20&include_entities=true';
  } else {
    $twitterapi = 'https://api.twitter.com/1/lists/statuses.json?slug=unl&owner_screen_name=unlnews&page=1&include_entities=true';
  }
  $proxy = $base_path.'sites/all/themes/unl_digitalsignage/proxy.php?u='.urlencode($twitterapi);
  return drupal_add_js('UNL.digitalSignage.feeds["'.$fieldname.'"] = "'.$proxy.'"', 'inline');
}