<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}


qa_register_plugin_module('page', 'qa-zwp-page.php', 'qa_zwp_page', 'Zwp Page');
qa_register_plugin_phrases('qa-zwp-lang-*.php', 'zwp_page');
