<?php

/**
 * Head Tag Module
 *
 * This module provides simplified interface for adding links and scripts.
 *
 * Events provided by this module:
 *  - before-print
 *		Event triggered before head tags are rendered to final template.
 *
 * Author: Mladen Mijatov
 */
use Core\Events;
use Core\Module;


class head_tag extends Module {
	private static $_instance;
	private $tags = array();
	private $meta_tags = array();
	private $link_tags = array();
	private $script_tags = array();
	private $closeable_tags = array('script', 'style', 'title');

	private $title_parts = array();
	private $title_glue = ' - ';

	private $analytics = null;
	private $analytics_domain = null;
	private $analytics_version = 'v1';

	private $optimizer = null;
	private $optimizer_key = '';
	private $optimizer_page = '';
	private $optimizer_show_control = false;

	private $supported_styles = array('stylesheet', 'stylesheet/less');

	/**
	 * Constructor
	 */
	protected function __construct() {
		parent::__construct(__FILE__);

		// register events
		Events::register('head-tag', 'before-print');
		Events::register('head-tag', 'before-title-print');
	}

	/**
	 * Public function that creates a single instance
	 */
	public static function get_instance() {
		if (!isset(self::$_instance))
			self::$_instance = new self();

		return self::$_instance;
	}

	/**
	 * Transfers control to module functions
	 *
	 * @param array $params
	 * @param array $children
	 */
	public function transfer_control($params, $children) {
		if (isset($params['action']))
			switch ($params['action']) {
				case 'print_tag':
					trigger_error('Calling `print_tag` from `head_tag` module is deprecated, use `show`!', E_USER_WARNING);
					// skipped break on purpose, we still need to handle

				case 'show':
					$this->show_all_tags($params);
					break;

				case 'add_to_title':
					$this->add_attribute_to_title($params, $children);
					break;
			}
	}

	/**
	 * Redefine abstract methods
	 */
	public function initialize() {
	}

	public function cleanup() {
	}

	/**
	 * Add specified value as part of the title.
	 *
	 * @param string $value
	 */
	public function add_to_title($value) {
		$this->title_parts []= $value;
	}

	/**
	 * Add tag attribute content to page title.
	 *
	 * @param array $tag_params
	 * @param array $children
	 */
	private function add_attribute_to_title($tag_params, $children) {
		if (!isset($tag_params['value']))
			return;

		$this->title_parts []= fix_chars($tag_params['value']);
	}

	/**
	 * Adds head tag to the list
	 *
	 * @param string $name
	 * @param array $params
	 */
	public function add_tag($name, $params) {
		global $optimize_code, $section;

		$name = strtolower($name);
		$data = array($name, $params);

		switch ($name) {
			case 'meta':
				$this->meta_tags[] = $data;
				break;

			case 'link':
				// include LESS JavaScript parser
				$optimize_styles = $section == 'backend' || $optimize_code;
				if ((isset($params['rel']) && $params['rel'] == 'stylesheet/less') && !$optimize_styles) {
					$collection = collection::get_instance();
					$collection->includeScript(collection::LESS);
				}

				$this->link_tags[] = $data;
				break;

			case 'script':
				$this->script_tags[] = $data;
				break;

			default:
				$this->tags[] = $data;
				break;
		}
	}

	public function addTag($name, $params) {
		$this->add_tag($name, $params);
	}

	/**
	 * Add Google Analytics script to the page
	 *
	 * @param string $code
	 * @param string $domain
	 * @param string $version
	 */
	public function addGoogleAnalytics($code, $domain, $version) {
		$this->analytics = $code;
		$this->analytics_domain = $domain;
		$this->analytics_version = $version;
	}

	/**
	 * Add Google Site optimizer script to the page
	 *
	 * @param string $code
	 * @param string $key
	 * @param string $page
	 * @param boolean $show_control
	 */
	public function addGoogleSiteOptimizer($code, $key, $page, $show_control) {
		$this->optimizer = $code;
		$this->optimizer_key = $key;
		$this->optimizer_page = $page;
		$this->optimizer_show_control = $show_control;
	}

	/**
	 * Show specified tag.
	 *
	 * @param object $tag
	 */
	private function print_tag($tag, $body=null) {
		$params = $tag[1];
		$tag_params = '';

		// generate params if needed
		if (count($params) > 0)
			foreach ($params as $param => $value)
				$tag_params .= ' '.$param.'="'.$value.'"';

		print "<{$tag[0]}{$tag_params}>";
		print in_array($tag[0], $this->closeable_tags) || !is_null($body) ? "{$body}</{$tag[0]}>" : "";
	}

	/**
	 * Show tags previously added. This function also emits two events, before title and
	 * before print giving additional opportunity for modules to add their tags.
	 *
	 * @param array $params
	 */
	private function show_all_tags($params=array()) {
		global $optimize_code, $section;

		// take flags from params
		$show_title = isset($params['title']) ? $params['title'] == 1 : true;
		$show_scripts = isset($params['scripts']) ? $params['scripts'] == 1 : true;
		$show_styles = isset($params['styles']) ? $params['styles'] == 1 : true;
		$show_other = isset($params['other']) ? $params['other'] == 1 : true;

		// determine whether we should optimize code
		if ($section != 'backend') {
			$optimize_styles = $optimize_code && !defined('DEBUG');
			$optimize_scripts = $optimize_code && !defined('DEBUG');

		} else {
			$optimize_styles = true;
			$optimize_scripts = false;
		}

		// give modules chance to set title if needed
		$response_list = Events::trigger('head-tag', 'before-title-print');
		$priority = 0;
		$title_body = null;

		// get title from the event results
		if (count($response_list) > 0)
			foreach ($response_list as $response)
				if ($response[1] > $priority) {
					$priority = $response[1];
					$title_body = $response[0];
				}

		if (is_null($title_body)) {
			// generate title from fragments provided by template
			$title_body = Language::get_text('site_title');
			if (count($this->title_parts) > 0)
				$title_body .= $this->title_glue.implode($this->title_glue, $this->title_parts);
		}

		// give modules chance to add elements
		Events::trigger('head-tag', 'before-print');

		// print title tag
		if ($show_title)
			$this->print_tag(array('title', array()), $title_body);

		// get instance of code optimizer
		$optimizer = CodeOptimizer::get_instance();

		// list of tags to show as they are
		$unhandled_tags = $this->tags;

		// show meta tags first
		foreach ($this->meta_tags as $tag)
			$this->print_tag($tag);

		// show styles
		if ($show_styles) {
			if (!$optimize_styles) {
				// show style tags as they are when specified
				$unhandled_tags = array_merge($unhandled_tags, $this->link_tags);

			} else {
				// add each style for compilation
				foreach ($this->link_tags as $link) {
					$can_be_compiled = isset($link[1]['rel']) && in_array($link[1]['rel'], $this->supported_styles);

					if ($can_be_compiled)
						$added = $optimizer->add_style($link[1]['href']);

					if (!$can_be_compiled || !$added)
						$unhandled_tags [] = $link;
				}

				// print optimized code
				$optimizer->print_style_data();
			}
		}

		// show scripts
		if ($show_scripts) {
			if (!$optimize_code) {
				// show script tags as they are when specified
				if ($show_scripts)
					$unhandled_tags = array_merge($unhandled_tags, $this->script_tags);

			} else {
				// add each script link for compilation
				$handled_tags = array();
				foreach ($this->script_tags as $script)
					if (!$optimizer->add_script($script[1]['src']))
						$unhandled_tags []= $script; else
						$handled_tags []= $script;  // collect scripts in case compile fails

				// print optimized code
				try {
					$optimizer->print_script_data();

				} catch (ScriptCompileError $error) {
					// handle issue with compiling code
					trigger_error($error->getMessage(), E_USER_WARNING);
					$unhandled_tags = array_merge($unhandled_tags, $handled_tags);
				}
			}
		}

		// show unhandled tags
		foreach ($unhandled_tags as $tag)
			$this->print_tag($tag);

		// print google analytics code if needed
		if (!is_null($this->analytics) && $show_other) {
			$template = new TemplateHandler("google_analytics_{$this->analytics_version}.xml", $this->path.'templates/');
			$template->set_mapped_module($this->name);

			$params = array(
						'code'   => $this->analytics,
						'domain' => $this->analytics_domain
					);

			$template->restore_xml();
			$template->set_local_params($params);
			$template->parse();
		}

		// print google site optimizer code if needed
		if (!is_null($this->optimizer) && $show_other) {
			$template = new TemplateHandler('google_site_optimizer.xml', $this->path.'templates/');
			$template->set_mapped_module($this->name);

			$params = array(
							'code'         => $this->optimizer,
							'key'          => $this->optimizer_key,
							'page'         => $this->optimizer_page,
							'show_control' => $this->optimizer_show_control
						);

			$template->restore_xml();
			$template->set_local_params($params);
			$template->parse();
		}
	}
}
?>
