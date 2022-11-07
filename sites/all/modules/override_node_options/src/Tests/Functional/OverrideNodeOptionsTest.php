<?php

/**
 * @file
 * Unit tests for the override_node_options module.
 */

namespace Drupal\override_node_options\Tests\Functional;

/**
 * Defines a base class for testing the Override Node Options module.
 */
class OverrideNodeOptionsTest extends \DrupalWebTestCase {

  /**
   * A standard user with basic permissions.
   *
   * @var \stdClass
   */
  protected $normalUser;

  /**
   * A page node to test against.
   *
   * @var \stdClass
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Override node options',
      'description' => 'Functional tests for overriding options on node forms.',
      'group' => 'Override node options',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp('override_node_options');

    $this->normalUser = $this->drupalCreateUser(array('create page content', 'edit any page content'));
    $this->node = $this->drupalCreateNode();
  }

  /**
   * Assert that fields in a node were updated to certain values.
   *
   * @param \stdClass $node
   *   The node object to check (will be reloaded from the database).
   * @param array $fields
   *   An array of values to check equality, keyed by node object property.
   */
  private function assertNodeFieldsUpdated(\stdClass $node, array $fields) {
    // Re-load the node from the database to make sure we have the current
    // values.
    $node = node_load($node->nid, NULL, TRUE);
    foreach ($fields as $field => $value) {
      $this->assertEqual(
        $node->$field,
        $value,
        t('Node @field was updated to !value, expected !expected.', array(
          '@field' => $field,
          '!value' => var_export($node->$field, TRUE),
          '!expected' => var_export($value, TRUE),
        ))
      );
    }
  }

  /**
   * Assert that the user cannot access fields on node add and edit forms.
   *
   * @param \stdClass $node
   *   The node object, will be used on the node edit form.
   * @param array $fields
   *   An array of form fields to check.
   */
  private function assertNodeFieldsNoAccess(\stdClass $node, array $fields) {
    $this->drupalGet("node/add/{$node->type}");
    foreach ($fields as $field) {
      $this->assertNoFieldByName($field);
    }

    $this->drupalGet("node/{$this->node->nid}/edit");
    foreach ($fields as $field) {
      $this->assertNoFieldByName($field);
    }
  }

  /**
   * Helper function that generates combinations.
   */
  private static function combinations($values, $length, $start = 0) {
    $results = array();
    if ($start < $length) {
      $inner = self::combinations($values, $length, $start + 1) ?: array(array());
      foreach ($values as $value) {
        foreach ($inner as $result) {
          $results[] = array_merge(array($value), $result);
        }
      }
    }
    return $results;
  }

  /**
   * Test the 'Authoring information' fieldset.
   */
  protected function testNodeOptions() {
    $common = array(
      'create page content',
      'edit any page content',
    );
    $template = array(
      'override %s published option',
      'override %s promote to front page option',
      'override %s sticky option',
      'override %s comment setting option',
    );
    $combinations = self::combinations(array('page', 'all'), 4);
    $cases = array();
    foreach ($combinations as $combination) {
      $permissions = $common;
      foreach ($combination as $index => $value) {
        $permissions[] = sprintf($template[$index], $value);
      }
      $cases[] = $permissions;
    }

    foreach ($cases as $permissions) {
      $account = $this->drupalCreateUser($permissions);
      $this->drupalLogin($account);

      $fields = array(
        'status' => !$this->node->status,
        'promote' => !$this->node->promote,
        'sticky' => !$this->node->sticky,
        'comment' => COMMENT_NODE_OPEN,
      );
      $this->drupalPost("node/{$this->node->nid}/edit", $fields, t('Save'));
      $this->assertNodeFieldsUpdated($this->node, $fields);
    }

    $this->drupalLogin($this->normalUser);
    $this->assertNodeFieldsNoAccess($this->node, array_keys($fields));
  }

  /**
   * Test the 'Revision information' fieldset.
   */
  protected function testNodeRevisions() {
    $specific_user = $this->drupalCreateUser(array(
      'create page content',
      'edit any page content',
      'override page revision option',
    ));

    $general_user = $this->drupalCreateUser(array(
      'create page content',
      'edit any page content',
      'override all revision option',
    ));

    foreach (array($specific_user, $general_user) as $account) {
      $this->drupalLogin($account);

      // Ensure that we have the latest node data.
      $node = node_load($this->node->nid, NULL, TRUE);

      $fields = array(
        'revision' => TRUE,
      );
      $this->drupalPost("node/{$node->nid}/edit", $fields, t('Save'));
      $this->assertNodeFieldsUpdated($node, array('vid' => $node->vid + 1));
    }

    $this->drupalLogin($this->normalUser);
    $this->assertNodeFieldsNoAccess($this->node, array_keys($fields));
  }

  /**
   * Test the 'Authoring information' fieldset.
   */
  protected function testNodeAuthor() {
    $specific_user = $this->drupalCreateUser(array(
      'create page content',
      'edit any page content',
      'override page authored on option',
      'override page authored by option',
    ));

    $general_user = $this->drupalCreateUser(array(
      'create page content',
      'edit any page content',
      'override all authored on option',
      'override all authored by option',
    ));

    foreach (array($specific_user, $general_user) as $account) {
      $this->drupalLogin($account);

      $this->drupalPost("node/{$this->node->nid}/edit", array('name' => 'invalid-user'), t('Save'));
      $this->assertText('The username invalid-user does not exist.');

      $this->drupalPost("node/{$this->node->nid}/edit", array('date' => 'invalid-date'), t('Save'));
      $this->assertText('You have to specify a valid date.');

      $time = time() + 500;
      $fields = array(
        'name' => '',
        'date' => format_date($time, 'custom', 'Y-m-d H:i:s O'),
      );
      $this->drupalPost("node/{$this->node->nid}/edit", $fields, t('Save'));
      $this->assertNodeFieldsUpdated($this->node, array('uid' => 0, 'created' => $time));
    }

    $this->drupalLogin($this->normalUser);
    $this->assertNodeFieldsNoAccess($this->node, array_keys($fields));
  }

  /**
   * Ensure that the node created date does not change when the node is edited.
   */
  public function testNodeCreatedDateDoesNotChange() {
    $this->drupalLogin(
      $this->drupalCreateUser(array('edit any page content'))
    );

    $node = $this->drupalCreateNode();

    // Update the node.
    $this->drupalPost("node/{$node->nid}/edit", array(), t('Save'));

    // Load a new instance of the node.
    $node2 = node_load($node->nid);

    // Ensure that the node was updated by comparing the changed dates, but the
    // created dates still match.
    $this->assertNotEqual($node->changed, $node2->changed, t('Changed values do not match.'));
    $this->assertEqual($node->created, $node2->created, t('Created values do match.'));
  }

}
