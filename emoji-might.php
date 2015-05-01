<?php
/*
Plugin Name: Emoji 💪
Plugin URI: https://github.com/marsjaninzmarsa/WordPress-Emoji-Might
Description: WordPress 4.2+ plugin for delivering alternative Emoji sets, like Emojione
Version: 0.1.0
Author: Jakub Niewiarowski
Author URI: http://niewiarowski.it
License: GPL2
Text Domain: mj-emoji-might
Domain Path: /lang/
*/


class MjEmojiMight
{
	
	function __construct()
	{
		add_filter('emoji_url', array($this, 'emoji_url'));
		add_filter('emoji_ext', array($this, 'emoji_ext'));

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'settings_init'));

		$this->options = get_option('mj-emoji-might-options', $this->defaults);
	}

	/////////////
	// Assets. //
	/////////////

	private $emoji_sets = array(
		'twemoji' => array(
			'name' => 'Twitter Emoji (Twemoji)',
			'cdn'  => array(
				'wp-org'  => array(
					'name'  => 'WordPress.org',
					'url'   => '//s.w.org/images/core/emoji/%size%/',
					'sizes' => array(
						'72x72',
					),
				),
				'maxcdn' => array(
					'name'  => 'MaxCDN',
					'url'   => '//twemoji.maxcdn.com/%size%/',
					'sizes' => array(
						'16x16',
						'36x36',
						'72x72',
						'svg',
					),
				),
				'cdnjs' => array(
					'name'  => 'cdnjs',
					'url'   => '//cdnjs.cloudflare.com/ajax/libs/twemoji/1.4.1/%size%/',
					'sizes' => array(
						'16x16',
						'36x36',
						'72x72',
						'svg',
					),
				),
			),
			'demo' => 'https://twitter.github.io/twemoji/preview.html',
			'example' => 'https://s.w.org/images/core/emoji/72x72/1f60d.png',
		),
		'emojione' => array(
			'name' => 'Emoji One',
			'cdn'  => array(
				'jsdelivr' => array(
					'name'  => 'JSDelivr',
					'url'   => 'http://cdn.jsdelivr.net/emojione/assets/%ext%/',
					'sizes' => array(
						'64x64',
						'svg',
					),
				),
			),
			'demo' => 'http://emojione.com/demo',
			'example' => 'http://cdn.jsdelivr.net/emojione/assets/svg/1F60D.svg',
		),
	);

	private $defaults = array(
		'set'  => 'twemoji',
		'cdn'  => 'wp-org',
		'size' => '72x72',
	);

	private $options = array();

	////////////////////
	// Settings page. //
	////////////////////

	public function add_admin_menu() {
		add_options_page('Emoji 💪', 'Emoji 💪', 'manage_options', 'emoji_might', array($this, 'options_page'));
	}

	public function settings_init() {
		register_setting('mj-emoji-might', 'mj-emoji-might-options', array($this, 'settings_callback'));
		add_settings_section(
			'mj-emoji-might-section',
			__( 'Emoji settings', 'mj-emoji-might' ),
			array($this, 'section_callback'),
			'emoji_might'
		);
		add_settings_field(
			'mj-emoji-might-set',
			__( 'Emoji set', 'mj-emoji-might' ),
			array($this, 'set_chooser_callback'),
			'emoji_might',
			'mj-emoji-might-section'
		);
		add_settings_field(
			'mj-emoji-might-cdn',
			__( 'Images source (CDN)', 'mj-emoji-might' ),
			array($this, 'cdn_chooser_callback'),
			'emoji_might',
			'mj-emoji-might-section'
		);
		add_settings_field(
			'mj-emoji-might-size',
			__( 'Size of images', 'mj-emoji-might' ),
			array($this, 'size_chooser_callback'),
			'emoji_might',
			'mj-emoji-might-section'
		);
	}

	public function section_callback() {
		_e('Choose your favourite Emoji set.');
	}

	public function set_chooser_callback() {
		$options = $this->options;
		foreach ($this->emoji_sets as $set => $data) {
			printf(
				'<input type="radio" name="mj-emoji-might-options[set]" id="mj-emoji-might-set-%s" value="%s" required %s />',
				$set, $set, checked($options['set'], $set, 0)
			);
			printf(
				'<label for="mj-emoji-might-set-%s"><img src="%s" style="width: 1em;" alt="😍" /> %s</label>',
				$set, $data['example'], $data['name']
			);
			printf(
				'<a href="%s" target="_blank">Demo page</a>',
				$data['demo']
			);
		}
	}

	public function cdn_chooser_callback() {
		$options = $this->options;
		foreach ($this->emoji_sets as $set => $data) {
			printf('<fieldset id="mj-emoji-might-cdn-for-%s">', $set);
			foreach ($data['cdn'] as $cdn => $cdn_data) {
				printf(
					'<input type="radio" name="mj-emoji-might-options[cdn]" id="mj-emoji-might-%s-%s" value="%s" required %s />',
					$set, $cdn, $cdn, checked($options['set'].$options['cdn'], $set.$cdn, 0)
				);
				printf(
					'<label for="mj-emoji-might-%s-%s">%s (%s: %s)</label>',
					$set, $cdn, $cdn_data['name'],
					_n('Available size', 'Available sizes', count($cdn_data['sizes']), 'mj-emoji-might'),
					implode(', ', $cdn_data['sizes'])
				);
			}
			printf('</fieldset>');
		}
	}

	public function size_chooser_callback() {
		$options = $this->options;
		$sizes = array();
		foreach ($this->emoji_sets as $set => $data) {
			foreach ($data['cdn'] as $cdn => $data) {
				foreach ($data['sizes'] as $size) {
					$sizes[$size][] = $set.$cdn;
				}
			}
		}
		// var_dump($sizes);
		ksort($sizes);
		// var_dump($sizes);
		print('<select name="mj-emoji-might-options[size]" required>');
		foreach ($sizes as $size => $set_cdn) {
			printf(
				'<option %s %s>%s</option>',
				(in_array($size, $this->emoji_sets[$options['set']]['cdn'][$options['cdn']]['sizes'])) ? '' : 'disabled',
				selected(in_array($options['set'].$options['cdn'], $set_cdn), true, 0),
				$size
			);
		}
		print('</select>');
	}

	public function options_page() {
		?>
		<form action='options.php' method='post'>
			
			<h2>Emoji 💪</h2>
			
			<?php
			settings_fields('mj-emoji-might');
			do_settings_sections('emoji_might');
			submit_button();
			?>
			
		</form>
		<?php
	}

	public function settings_callback($input) {
		$new_input  = array();
		$defaults   = $this->defaults;
		$emoji_sets = $this->emoji_sets;
		$new_input['set'] =
			(isset($input['set'], $emoji_sets[$input['set']])) ?
				$input['set']:
				$defaults['set'];
		$new_input['cdn'] =
			(isset($input['cdn'], $emoji_sets[$new_input['set']]['cdn'][$input['cdn']])) ?
				$input['cdn']:
				$defaults['cdn'];
		$new_input['size'] =
			(isset($input['size']) && in_array($input['size'], $emoji_sets[$new_input['set']]['cdn'][$new_input['cdn']]['sizes'])) ?
				$input['size']:
				$defaults['size'];
		return $new_input;
	}


	/////////////////////
	// Actuall plugin. //
	/////////////////////

	public function emoji_url($url) {
		$options = $this->options;
		if(isset($this->emoji_sets[$options['set']]['cdn'][$options['cdn']]['url'])) {
			$url = $this->emoji_sets[$options['set']]['cdn'][$options['cdn']]['url'];
			$url = str_replace('%size%', $options['size'], $url);
			$url = str_replace('%ext%',  $this->emoji_ext(), $url);
			$url = set_url_scheme($url);
		}
		return $url;
	}

	public function emoji_ext() {
		$options = $this->options;
		$ext = ($options['size'] == 'svg') ? 'svg' : 'png';
		return '.'.$ext;
	}
}

$mj_emoji_might = new MjEmojiMight();