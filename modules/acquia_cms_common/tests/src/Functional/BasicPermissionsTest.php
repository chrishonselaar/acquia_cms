<?php

namespace Drupal\Tests\acquia_cms_common\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests basic, broad permissions of the user roles included with Acquia CMS.
 *
 * @group acquia_cms_common
 * @group acquia_cms
 */
class BasicPermissionsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'acquia_cms_common',
    'toolbar',
  ];

  /**
   * Disable strict config schema checks in this test.
   *
   * Cohesion has a lot of config schema errors, and until they are all fixed,
   * this test cannot pass unless we disable strict config schema checking
   * altogether. Since strict config schema isn't critically important in
   * testing this functionality, it's okay to disable it for now, but it should
   * be re-enabled (i.e., this property should be removed) as soon as possible.
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Tests basic capabilities of our user roles.
   *
   * - Content authors, editors, and administrators should all be able to access
   *   the toolbar and the content overview.
   * - User administrator should be able to access the toolbar and the user
   *   overview.
   */
  public function testBasicPermissions() {
    $assert_session = $this->assertSession();

    $assert_toolbar = function () use ($assert_session) {
      $assert_session->elementExists('css', '#toolbar-administration');
    };

    $roles = [
      'content_author',
      'content_editor',
      'content_administrator',
    ];
    foreach ($roles as $role) {
      $account = $this->drupalCreateUser();
      $account->addRole($role);
      $account->save();
      // Only content administrators should be able to administer nodes.
      $this->assertSame($role === 'content_administrator', $account->hasPermission('administer nodes'));

      $this->drupalLogin($account);
      $assert_toolbar();
      $this->drupalGet('/admin/content');
      $assert_session->statusCodeEquals(200);
      $this->drupalLogout();
    }

    $account = $this->drupalCreateUser();
    $account->addRole('user_administrator');
    $account->save();
    $this->assertTrue($account->hasPermission('administer users'));

    $this->drupalLogin($account);
    $assert_toolbar();
    $this->drupalGet('/admin/people');
    $assert_session->statusCodeEquals(200);
  }

}