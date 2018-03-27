<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests that rendering of elements follows the theme implementation.
 */
class RenderingTest extends AbstractKernelTest implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_theme_rendering_test_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $structure
   *   The structure of the form, read from the fixtures files.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $structure = NULL): array {
    $form['test'] = $structure;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * Test rendering of elements.
   *
   * @param array $structure
   *   A render array.
   * @param array $contains_string
   *   Strings that need to be present.
   * @param array $contains_element
   *   Elements that need to be present.
   *
   * @throws \Exception
   *
   * @dataProvider renderingDataProvider
   */
  public function testRendering(array $structure, array $contains_string, array $contains_element): void {
    // Wrap all the test structure inside a form. This will allow proper
    // processing of form elements and invocation of form alter hooks.
    // Even if the elements being tested are not form related, the form can
    // host them without causing any issues.
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$structure]);
    $form = $this->container->get('form_builder')->buildForm($this, $form_state, $structure);

    $html = $this->renderRoot($form);
    $crawler = new Crawler($html);

    foreach ($contains_string as $string) {
      $this->assertContains($string, $html);
    }

    foreach ($contains_element as $assertion) {
      $wrapper = $crawler->filter($assertion['filter']);
      $this->assertCount($assertion['expected_result'], $wrapper);
    }
  }

  /**
   * Data provider for rendering tests.
   *
   * The actual data is read from fixtures stored in a YAML configuration.
   *
   * @return array
   *   A set of dump data for testing.
   */
  public function renderingDataProvider(): array {
    return $this->getFixtureContent('rendering.yml');
  }

}
