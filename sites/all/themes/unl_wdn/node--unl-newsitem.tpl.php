<?php
/**
 * @file
 * unl_wdn theme implementation to display a news item node.
 */
?>
<div id="node-<?php print $node->nid; ?><?php print ($view_mode != 'full' ? '-teaser' : ''); ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <?php if ($view_mode != 'full'): ?>
    <h4<?php print $title_attributes; ?>>
      <?php if ($view_mode == 'abbr_teaser'): ?>
        <?php
        print render($content["field_unl_newsimg"][0]);
        ?>
      <?php endif; ?>
      <a href="<?php print $node_url; ?>"><?php print $title; ?></a>
    </h4>
  <?php endif; ?>
  <?php print render($title_suffix); ?>

  <?php if ($view_mode == 'full'): ?>
    <?php print render($content["field_unl_imgcar"]); ?>
  <?php endif; ?>

  <?php if ($view_mode == 'full' || $view_mode == 'teaser' && $display_submitted): ?>
    <div class="meta submitted">
      <?php print $submitted; ?>
    </div>
  <?php endif; ?>

  <div class="content clearfix"<?php print $content_attributes; ?>>
    <?php if ($view_mode == 'full'): ?>
      <div class="field field-type-image">
        <div class="field-items">
          <div class="field-item even primary-image">
          <?php
            $content["field_unl_newsimg"][0]["#image_style"] = 'large';
            $content["field_unl_newsimg"][0]["#item"]["attributes"]["class"] = array('frame');
            print render($content["field_unl_newsimg"][0]);
          ?>
          <?php if ($page && !empty($content["field_unl_newsimg"][0]["#item"]["title"])): ?>
            <p class="caption">
              <?php print $content["field_unl_newsimg"][0]["#item"]["title"]; ?>
            </p>
          <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php
      // We hide the comments and links now so that we can render them later.
      hide($content['comments']);
      hide($content['links']);

      // If more than one image is available, show the addtional images
      if ($view_mode == 'full') {
        foreach (array_diff(array_keys($content), array('field_unl_imgcar', 'field_unl_newsimg', 'links', 'comments')) as $field) {
          print render($content[$field]);
        }
        if (isset($content["field_unl_newsimg"][1])) {
          unset($content["field_unl_newsimg"][0]);
          print render($content["field_unl_newsimg"]);
        }
      } else if ($view_mode == 'teaser') {
        print render($content["field_unl_newsimg"]);
        print render($content["body"]);
      }

    ?>
  </div>

  <?php print render($content['links']); ?>

  <?php print render($content['comments']); ?>

</div>
