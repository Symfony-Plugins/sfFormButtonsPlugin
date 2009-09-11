<?php
/**
 * Returns an XHTML compliant <button> tag with type="submit".
 *
 * By default, this helper creates a submit tag with a name of <em>commit</em> to avoid
 * conflicts with other parts of the framework. It is recommended that you do not use the name
 * "submit" for submit tags unless absolutely necessary. Also, the default <i>$value</i> parameter
 * (title of the button) is set to "Save changes", which can be easily overwritten by passing a
 * <i>$value</i> parameter.
 *
 * <b>Examples:</b>
 * <code>
 *  echo submit_button_tag();
 * </code>
 *
 * <code>
 *  echo submit_button_tag('Update Record');
 * </code>
 *
 * @param  string $value    field value (title of submit button)
 * @param  array  $options  additional HTML compliant <button> tag parameters
 *
 * @return string XHTML compliant <button> tag with type="submit"
 */
function submit_button_tag($value = 'Save changes', $options = array())
{
  return content_tag('button', $value, array_merge(array('type' => 'submit', 'name' => 'commit'), _convert_options_to_javascript(_convert_options($options))));
}

/**
 * Returns an XHTML compliant <button> tag with type="button".
 *
 * By default, this helper creates a button tag with a name of $value replacing spaces with underscores,
 * but you can avoid it specifying it in the $options parameter.
 *
 * <b>Examples:</b>
 * <code>
 *  echo button_tag('My Button'); // name = my_button_btn
 * </code>
 *
 * <code>
 *  echo button_tag('Update Record', array('name' => 'my_update_button'));
 * </code>
 *
 * @param  string $value    field value (title of button)
 * @param  array  $options  additional HTML compliant <button> tag parameters
 *
 * @return string XHTML compliant <button> tag with type="button"
 */
function button_tag($value, $options = array())
{
  if (!isset($options['name']) || !$options['name']) {
    if (false === strpos($value, '<img '))
    {
      $options['name'] = _button_tag_name($value);
    }
  }

  return content_tag('button', $value, array_merge(array('type' => 'button'), _convert_options_to_javascript(_convert_options($options))));
}

/**
 * Returns a button that'll trigger a javascript function using the
 * onclick handler and return false after the fact.
 *
 * Examples:
 *   <?php echo button_to_js('Greeting', "alert('Hello world!')") ?>
 */
function button_to_js($value, $function, $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options['type']    = 'button';
  $html_options['onclick'] = $function.'; return false;';

  if (!isset($html_options['name']) || !$html_options['name']) {
    if (false === strpos($value, '<img '))
    {
      $html_options['name'] = _button_tag_name($value);
    }
  }

  return content_tag('button', $value, $html_options);
}

/**
 * Creates an <button>button</button> tag of the given name pointing to a routed URL
 * based on the module/action passed as argument and the routing configuration.
 *
 * <b>Options:</b>
 * - 'absolute' - if set to true, the helper outputs an absolute URL
 * - 'query_string' - to append a query string (starting by ?) to the routed url
 * - 'anchor' - to append an anchor (starting by #) to the routed url
 * - 'confirm' - displays a javascript confirmation alert when the button is clicked
 * - 'popup' - if set to true, the button opens a new browser window
 * - 'post' - if set to true, the button submits a POST request instead of GET (caution: do not use inside a form)
 *
 * <b>Examples:</b>
 * <code>
 *  echo button_to_url('Delete this page', 'my_module/my_action');
 *    => <button name="delete_this_page_btn" type="button" onclick="document.location.href='/path/to/my/action';">Delete this page</button>
 * </code>
 *
 * @param  string $value         value of the button (used in "name" attribute if not set)
 * @param  string $internal_uri  'module/action' or '@rule' of the action
 * @param  array  $options       additional HTML compliant <button> tag parameters
 * @return string XHTML compliant <button>button tag</button>
 * @see    url_for, link_to
 */
function button_to_url($value, $internal_uri, $options = array())
{
  $html_options = _parse_attributes($options);

  if (!isset($html_options['name']) || !$html_options['name']) {
    if (false === strpos($value, '<img '))
    {
      $html_options['name'] = _button_tag_name($value);
    }
  }

  if (isset($html_options['post']) && $html_options['post']) {
    if (isset($html_options['popup'])) {
      throw new sfConfigurationException('You can\'t use "popup" and "post" together.');
    }
    $html_options['type'] = 'submit';
    unset($html_options['post']);
    $html_options = _convert_options_to_javascript($html_options);

    return form_tag($internal_uri, array('method' => 'post', 'class' => 'button_to')).content_tag('button', $value, $html_options).'</form>';
  }

  $url = url_for($internal_uri);
  if (isset($html_options['query_string'])) {
    $url = $url.'?'.$html_options['query_string'];
    unset($html_options['query_string']);
  }
  if (isset($html_options['anchor'])) {
    $url = $url.'#'.$html_options['anchor'];
    unset($html_options['anchor']);
  }
  $url = "'".$url."'";
  $html_options['type'] = 'button';
  if (isset($html_options['popup'])) {
    $html_options = _convert_options_to_javascript($html_options, $url);
    unset($html_options['popup']);
  }
  else
  {
    $html_options['onclick'] = "document.location.href=".$url.";";
    $html_options = _convert_options_to_javascript($html_options);
  }

  return content_tag('button', $value, $html_options);
}

function _button_tag_name($value)
{
  return strtolower(preg_replace('/\s+/', '_', $value)).'_btn';
}

/**
 *  Returns a <button>button tag</button> that will submit form using XMLHttpRequest in the background instead of regular
 *  reloading POST arrangement. The '$options' argument is the same as in 'form_remote_tag()'.
 *
 *  <b>Example:</b>
 *  <code>
 *  <?php echo submit_button_to_remote('name', 'value' array(
 *    'url'      => '@tag_add',
 *    'update'   => 'question_tags',
 *    'loading'  => "Element.show('indicator'); \$('tag').value = ''",
 *    'complete' => "Element.hide('indicator');".visual_effect('highlight', 'question_tags'),
 *  )) ?>
 *  </code>
 *  @uses JavascriptHelper.php
 *  @see  submit_to_remote, submit_image_to_remote
 *
 *  @param  string  $content  content of the button, can be pure string, <img /> image tags or any allowed nested tags for <button></button>
 *  @param  string  $value    value of the button
 *  @param  array   options   above JavaScript-related configuration options
 *  @param  array   options_html HTML-compliant additional tag attributes
 *  @return string  XHTML-compliant <button>button tag</button> triggering an background AJAX-submit og the form
 */
function submit_button_to_remote($content, $value = 'Save', $options, $options_html= array())
{
  $options = _parse_attributes($options);
  $options_html = _parse_attributes($options_html);

  if (!isset($options['with']))
  {
    $options['with'] = 'Form.serialize(this.form)';
  }

  $options_html['type'] = 'submit';
  $options_html['onclick'] = remote_function($options).' return false;';
  $options_html['name'] = 'submit';
  $options_html['value'] = $value;

  return content_tag('button', $content, _convert_options_to_javascript(_convert_options($options_html)));
}

?>