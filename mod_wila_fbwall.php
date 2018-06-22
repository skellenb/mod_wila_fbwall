<?php
/**
 * @version  $Id$
 * @author  WILA-WEB
 * @package  Joomla.Site
 * @subpackage mod_wila_fbwall
 * @copyright Copyright (C) 2017 by WILA-WEB. All rights reserved.
 * @license  http://www.gnu.org/licenses/gpl.html
 */
 
// No direct access
defined('_JEXEC') or die;

$fb_username = $params->get('fb_username', 'sonja.luethi.sg');
$fb_app_id = $params->get('fb_app_id', '1998570413756466');
$fb_app_secret = $params->get('fb_app_secret', 'f4bc61b18d6711768f8cb60596124cca');
$fb_app_token = $params->get('fb_app_token', '1998570413756466|q9B5uCkD3DL6SF1WRsRDO-mtb3M');
$fb_num_posts = $params->get('fb_num_posts', '20');
$load_jquery = $params->get('load_jquery', '1');



$profilepicture = get_raw_facebook_avatar_url($fb_username);

if ($load_jquery == 1) {
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>'."\n";
}
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.27.4/js/uikit.js"></script>'."\n";
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.27.4/css/uikit.css" />'."\n";

echo <<<END

<style>
.sixteen-nine {
  position: relative;
}
.sixteen-nine:before {
  display: block;
  content: "";
  width: 100%;
  padding-top: 56.25%;
}
.sixteen-nine > .content {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}
</style>
END;


?>
<div class="uk-grid uk-grid-small uk-grid-match uk-grid uk-grid-width-1-1 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3 uk-grid-width-xlarge-1-3" data-uk-grid-margin data-uk-grid-match={target:'.uk-panel'}>

<?php
require_once dirname(__FILE__) . '/php-graph-sdk-5.5/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
    'default_graph_version' => 'v2.10',
]);

$token = $fb_app_token;

try {
    // Returns a `Facebook\FacebookResponse` object
    $response = $fb->get('/'.$fb_username.'?fields=id,name,posts.limit('.$fb_num_posts.'),picture.type(large)', $token);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

$user = $response->getGraphUser();
$results = $response->getRequest();
$response = $fb->get('/'.$fb_username.'?fields=posts.limit('.$fb_num_posts.'){full_picture,picture,message,story,link,caption,name,source,description,created_time}', $token);
//$response = $fb->get('/'.$username.'?fields=posts{id,promotion_status,target,shares,message_tags,source,place,event,parent_id,reactions.limit(0).summary(true),child_attachments,message,caption,story,full_picture,picture,link,name,description,type,status_type,created_time,comments.limit(0).summary(true),sharedposts,privacy,updated_time,story_tags,permalink_url,coordinates}', $token);
//$response = $fb->get('/'.$username.'?fields=id&limit=10', $token);


$graphObject = $response->getGraphObject();

$response = $response->getBody();
$response = json_decode( $response, true );

$posts = $response['posts']['data'];

setlocale(LC_TIME, "de_DE");

foreach ($posts as $post) {

list($width, $height) = getimagesize($post[full_picture]);


//  echo "<!--";
//  print_r($post);
//  echo "-->";
    $ts = strtotime($post[created_time]);
    $timestamp = gmdate(DATE_ISO8601, $ts);
//  echo "<img src=\"$post[picture]\">";
?>
<?php if ($width > 10 && $height > 10) { ?>
    <div style="display: table-cell; position: relative;">
        <div class="uk-panel uk-panel-box uk-panel-box-secondary" style="border: 1px solid #ccc">
    <div class="user"><div class="userpic" style="height: 60px; width: 60px; display: block; float:left;"><img src="<?php echo $profilepicture; ?>" class="uk-border-circle"></div><div class="namedate" style="display: flex-center; height: 50px; padding-left: 10px;"><?php echo $user['name']; ?><br><?php echo strftime("%d. %B %Y %H:%M", $ts); ?></div></div>
         <?php if ($post[full_picture]) { ?>
            <a class="" target="_blank" href="<?php echo $post[link]; ?>">
                <figure class="uk-overlay uk-overlay-hover content"><img src="<?php echo $post[full_picture]; ?>" class="uk-overlay-scale" alt="<?php echo $post[story]; ?>" /></figure>
            </a>
         <?php } ?>
            <h3 class="blogtitle_large"><?php echo $post[name]; ?></h3>
            <h4 class="blogtitle_small"><?php echo substr($post[description],0,100); ?>...</h4>
            <div class="blog-text">
          <?php if($post[message]) { ?>
          <hr>
                <p><strong><?php echo substr($post[message],0,100); ?>...</strong></p>
          <?php } ?>
            </div>
        <a href="<?php echo $post[link]; ?>" target="_blank" class="uk-position-bottom-right linkbottom"><i class="uk-icon-chevron-circle-right"></i> Weiterlesen ...</a>
        </div>
    </div>
<?php } ?>
<?php


}


function get_raw_facebook_avatar_url($fb_username)
{
    $array = get_headers('https://graph.facebook.com/'.$fb_username.'/picture', 1);
    return (isset($array['Location']) ? $array['Location'] : FALSE);
}
?>
</div>
<div class="uk-text-center"><small><a href="https://cyberdine.ch">&copy; 2017 by Cyberdine Systems</a></small></div>
