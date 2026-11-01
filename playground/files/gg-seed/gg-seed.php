<?php
/**
 * Plugin Name: Growth Game Seed (ตัวอย่าง)
 * Description: Seeds demo courses, shop products, pages, and users for the Growth Game WordPress test site. Idempotent.
 * Version: 1.0.0
 * Author: Growth Game Co., Ltd. (test)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GG_SEED_VERSION', '1.0.0' );
define( 'GG_SEED_OPTION', 'gg_seed_version' );

add_action( 'init', 'gg_seed_maybe_run', 30 );
add_action( 'admin_notices', 'gg_seed_admin_banner' );

function gg_seed_admin_banner() {
	echo '<div class="notice notice-warning"><p><strong>Growth Game — สำเนาทดสอบ</strong> · เนื้อหาที่เป็นตัวอย่าง · ไม่ใช่เว็บจริง growthgame.co · ชำระเงินเป็นโหมดทดสอบเท่านั้น</p></div>';
}

function gg_seed_maybe_run() {
	// Seeding is driven by the Playground blueprint runPHP step to avoid double inserts.
	return;
}

function gg_seed_run() {
	if ( get_option( GG_SEED_OPTION ) === GG_SEED_VERSION ) {
		return;
	}
	gg_seed_configure_woocommerce();
	gg_seed_configure_tutor();
	gg_seed_users();
	$media = gg_seed_media();
	$pages = gg_seed_pages();
	$courses = gg_seed_courses( $media );
	$products = gg_seed_shop_products( $media );
	gg_seed_menus_and_front( $pages );
	gg_seed_gamipress();
	update_option( 'gg_seed_ids', array(
		'pages'    => $pages,
		'courses'  => $courses,
		'products' => $products,
		'media'    => $media,
	) );
}

function gg_seed_configure_woocommerce() {
	update_option( 'woocommerce_store_address', 'ตัวอย่าง — กรุงเทพฯ' );
	update_option( 'woocommerce_store_city', 'กรุงเทพฯ' );
	update_option( 'woocommerce_default_country', 'TH:TH-10' );
	update_option( 'woocommerce_store_postcode', '10110' );
	update_option( 'woocommerce_currency', 'THB' );
	update_option( 'woocommerce_price_thousand_sep', ',' );
	update_option( 'woocommerce_price_decimal_sep', '.' );
	update_option( 'woocommerce_price_num_decimals', '0' );
	update_option( 'woocommerce_weight_unit', 'kg' );
	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'woocommerce_calc_taxes', 'no' );
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_allow_tracking', 'no' );
	update_option( 'woocommerce_onboarding_profile', array( 'completed' => true, 'skipped' => true ) );
	update_option( 'woocommerce_task_list_hidden', 'yes' );
	update_option( 'woocommerce_task_list_complete', 'yes' );

	// Payment gateways — test modes only.
	update_option( 'woocommerce_bacs_settings', array(
		'enabled'      => 'yes',
		'title'        => 'โอนเงินผ่านธนาคาร (ทดสอบ)',
		'description'  => 'ตัวอย่างเท่านั้น — อย่าโอนเงินจริง',
		'instructions' => 'นี่คือโหมดทดสอบของสำเนาทดสอบ Growth Game',
		'account_details' => array(
			array(
				'account_name'   => 'Growth Game Test',
				'account_number' => '000-0-00000-0',
				'bank_name'      => 'ธนาคารตัวอย่าง',
				'sort_code'      => '',
				'iban'           => '',
				'bic'            => '',
			),
		),
	) );
	update_option( 'woocommerce_cod_settings', array(
		'enabled'     => 'yes',
		'title'       => 'เก็บเงินปลายทาง (ทดสอบ)',
		'description' => 'ใช้ทดสอบการสั่งซื้อสินค้าส่งถึงบ้านเท่านั้น',
		'instructions'=> 'ตัวอย่าง — ไม่มีการจัดส่งจริง',
		'enable_for_methods' => array(),
		'enable_for_virtual' => 'no',
	) );
	update_option( 'woocommerce_cheque_settings', array(
		'enabled'     => 'yes',
		'title'       => 'เช็ค / ทดสอบชำระ (สำหรับคอร์สออนไลน์)',
		'description' => 'เลือกช่องทางนี้เพื่อจำลองชำระเงินคอร์สในสำเนาทดสอบ',
		'instructions'=> 'ออเดอร์จะถูกตั้งเป็น Processing อัตโนมัติในโหมดทดสอบถ้าตั้งค่าไว้',
	) );

	if ( class_exists( 'WC_Install' ) ) {
		WC_Install::create_pages();
	}

	// Auto-complete virtual/downloadable orders for smoother demo.
	update_option( 'woocommerce_downloads_grant_access_after_payment', 'yes' );
}

function gg_seed_configure_tutor() {
	$opt = get_option( 'tutor_option', array() );
	if ( ! is_array( $opt ) ) {
		$opt = array();
	}
	$opt['monetize_by']                 = 'wc';
	$opt['enable_course_marketplace']   = 'off';
	$opt['enable_tutor_native_login']   = '1';
	$opt['student_must_login_to_view_course'] = '0';
	$opt['course_content_access_for_students'] = '1';
	$opt['enable_public_course']        = '1';
	update_option( 'tutor_option', $opt );

	// Ensure Tutor pages exist.
	if ( function_exists( 'tutor_utils' ) ) {
		$dashboard = tutor_utils()->get_tutor_dashboard_page_id();
		if ( ! $dashboard ) {
			$dash_id = wp_insert_post( array(
				'post_title'   => 'แดชบอร์ดคอร์ส',
				'post_name'    => 'dashboard-course',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[tutor_dashboard]',
			) );
			$opt['tutor_dashboard_page_id'] = $dash_id;
			update_option( 'tutor_option', $opt );
		}
	}
}

function gg_seed_users() {
	$admin_pass   = getenv( 'GG_ADMIN_PASS' ) ?: 'ChangeMeAdminLocalOnly1';
	$learner_pass = getenv( 'GG_LEARNER_PASS' ) ?: 'ChangeMeLearnerLocalOnly1';

	if ( ! username_exists( 'ggadmin' ) ) {
		$id = wp_create_user( 'ggadmin', $admin_pass, 'admin@growthgame.test' );
		if ( ! is_wp_error( $id ) ) {
			$user = new WP_User( $id );
			$user->set_role( 'administrator' );
			wp_update_user( array( 'ID' => $id, 'display_name' => 'Growth Game Admin', 'first_name' => 'Trang', 'last_name' => 'Test' ) );
		}
	} else {
		wp_set_password( $admin_pass, username_exists( 'ggadmin' ) );
		$user = new WP_User( username_exists( 'ggadmin' ) );
		$user->set_role( 'administrator' );
	}

	if ( ! username_exists( 'learner' ) ) {
		$id = wp_create_user( 'learner', $learner_pass, 'learner@growthgame.test' );
		if ( ! is_wp_error( $id ) ) {
			$user = new WP_User( $id );
			$user->set_role( 'customer' ); // Woo customer; Tutor treats as student
			wp_update_user( array( 'ID' => $id, 'display_name' => 'ผู้เรียนตัวอย่าง', 'first_name' => 'ผู้เรียน', 'last_name' => 'ตัวอย่าง' ) );
		}
	} else {
		wp_set_password( $learner_pass, username_exists( 'learner' ) );
	}

	// Also reset default admin password if present for local predictability when env set.
	if ( username_exists( 'admin' ) && getenv( 'GG_ADMIN_PASS' ) ) {
		wp_set_password( $admin_pass, username_exists( 'admin' ) );
	}
}

function gg_seed_media() {
	$ids = array();
	$upload = wp_upload_dir();
	$src_pdf = '/wordpress/wp-content/uploads/gg-seed/sample-canvas.pdf';
	// Prefer files written by blueprint into uploads.
	$candidates = array(
		'pdf'  => array( $upload['basedir'] . '/gg-seed/sample-canvas.pdf', ABSPATH . 'wp-content/uploads/gg-seed/sample-canvas.pdf' ),
		'logo' => array( $upload['basedir'] . '/gg-seed/logo.png', ABSPATH . 'wp-content/uploads/gg-seed/logo.png' ),
	);
	foreach ( $candidates as $key => $paths ) {
		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				$filetype = wp_check_filetype( basename( $path ), null );
				$attachment = array(
					'post_mime_type' => $filetype['type'],
					'post_title'     => sanitize_file_name( basename( $path ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				$attach_id = wp_insert_attachment( $attachment, $path );
				if ( ! is_wp_error( $attach_id ) ) {
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$meta = wp_generate_attachment_metadata( $attach_id, $path );
					wp_update_attachment_metadata( $attach_id, $meta );
					$ids[ $key ] = $attach_id;
				}
				break;
			}
		}
	}
	return $ids;
}

function gg_seed_page( $title, $slug, $content, $template = '' ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		wp_update_post( array( 'ID' => $existing->ID, 'post_title' => $title, 'post_content' => $content, 'post_status' => 'publish' ) );
		return $existing->ID;
	}
	$id = wp_insert_post( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $content,
	) );
	return $id;
}

function gg_seed_pages() {
	$home = gg_seed_page(
		'หน้าแรก',
		'home-gg',
		'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}},"color":{"background":"#132042"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#132042;padding-top:60px;padding-bottom:60px"><!-- wp:paragraph {"style":{"color":{"text":"#8FD0EA"},"typography":{"fontSize":"14px","letterSpacing":"0.12em","textTransform":"uppercase","fontWeight":"600"}}} -->
<p class="has-text-color" style="color:#8FD0EA;font-size:14px;font-weight:600;letter-spacing:0.12em;text-transform:uppercase">MOVE WITH MOTIVATION · ตัวอย่าง</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"color":{"text":"#ffffff"},"typography":{"fontSize":"42px","lineHeight":"1.25"}}} -->
<h1 class="wp-block-heading has-text-color" style="color:#ffffff;font-size:42px;line-height:1.25">ออกแบบแรงจูงใจด้วยกลไกเกม<br>ให้คนอยากทำ และองค์กรไปถึงเป้าหมาย</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#ECECEE"}}} -->
<p class="has-text-color" style="color:#ECECEE">Growth Game โดย ตรัง สุวรรณศิลป์ — ที่ปรึกษาเกมมิฟิเคชันและกลยุทธ์ · สำเนาทดสอบ WordPress (ไม่ใช่เว็บจริง)</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"color":{"background":"#DDD94A","text":"#132042"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/courses/" style="color:#132042;background-color:#DDD94A">ดูคอร์สเรียน →</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"color":{"text":"#8FD0EA"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-text-color wp-element-button" href="/shop/" style="color:#8FD0EA">ร้านค้า / หนังสือ →</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:heading -->
<h2 class="wp-block-heading">เราช่วยอะไรได้บ้าง</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">ที่ปรึกษาเกมมิฟิเคชัน</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>ออกแบบระบบแรงจูงใจให้ทีม/องค์กร เปลี่ยนพฤติกรรมที่อยากเห็นให้เกิดขึ้นจริง</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">อบรม &amp; คอร์ส</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>คลาสสด + คอร์สออนไลน์ มีใบรับรองและระบบแต้ม/แบดจ์ · <a href="/courses/">ดูคอร์ส</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">วางแผนกลยุทธ์ส่วนบุคคล</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Personal Strategy — วางเป้าหมายชีวิต/อาชีพด้วยกรอบคิดที่ชัด</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"style":{"border":{"color":"#5D68D8","width":"2px"},"spacing":{"padding":{"top":"24px","right":"24px","bottom":"24px","left":"24px"}}}} -->
<div class="wp-block-column has-border-color" style="border-color:#5D68D8;border-width:2px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:paragraph {"style":{"color":{"text":"#5D68D8"},"typography":{"fontWeight":"600","letterSpacing":"0.08em","textTransform":"uppercase","fontSize":"12px"}}} -->
<p class="has-text-color" style="color:#5D68D8;font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase">WEB APP</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">แอปออกแบบ Gamification</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>GoalGapGamify Designer <strong>ตัวอย่างชื่อ</strong> — ช่วยเดิน Goal → Gap → Gamify แล้วได้ canvas หนึ่งหน้า</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="/app/">ดูหน้าแนะนำแอป →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"border":{"color":"#343273","width":"2px"},"spacing":{"padding":{"top":"24px","right":"24px","bottom":"24px","left":"24px"}}}} -->
<div class="wp-block-column has-border-color" style="border-color:#343273;border-width:2px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px"><!-- wp:paragraph {"style":{"color":{"text":"#343273"},"typography":{"fontWeight":"600","letterSpacing":"0.08em","textTransform":"uppercase","fontSize":"12px"}}} -->
<p class="has-text-color" style="color:#343273;font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase">SHOP</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">ร้านค้า / หนังสือ</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>หนังสือ สินค้าส่งถึงบ้าน และไฟล์ดิจิทัล · ราคาเป็นตัวอย่าง</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><a href="/shop/">ไปที่ร้านค้า →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading {"anchor":"contact"} -->
<h2 class="wp-block-heading" id="contact">ติดต่อ</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>อีเมลตัวอย่าง: hello@growthgame.test · โทรตัวอย่าง: 02-000-0000 · นี่คือสำเนาทดสอบ</p>
<!-- /wp:paragraph -->'
	);

	$services = gg_seed_page( 'บริการ', 'services', '<!-- wp:heading --><h2>บริการ</h2><!-- /wp:heading --><!-- wp:paragraph --><p>ที่ปรึกษาเกมมิฟิเคชัน · อบรม · วางแผนกลยุทธ์ส่วนบุคคล · <strong>ตัวอย่าง</strong></p><!-- /wp:paragraph -->' );
	$courses_page = gg_seed_page( 'คอร์สเรียน', 'courses', '<!-- wp:heading --><h2>คอร์สเรียน &amp; คลาสสด</h2><!-- /wp:heading --><!-- wp:paragraph --><p>เรียนออนไลน์ได้ Certificate + แต้ม/แบดจ์ · เนื้อหาเป็นตัวอย่าง</p><!-- /wp:paragraph --><!-- wp:shortcode -->[tutor_course]<!-- /wp:shortcode -->' );
	$events = gg_seed_page( 'กิจกรรม', 'events', '<!-- wp:heading --><h2>กิจกรรม</h2><!-- /wp:heading --><!-- wp:paragraph --><p>คลาสสด Essentials / Masterclass ตามรอบปี · <strong>ตัวอย่าง</strong> — ยังไม่เปิดลงทะเบียนจริงบนสำเนานี้</p><!-- /wp:paragraph --><!-- wp:list --><ul><li>Gamification Essentials (onsite) — ตัวอย่างราคา 5,990–6,990 บาท</li><li>Masterclass — เร็วๆ นี้</li></ul><!-- /wp:list -->' );
	$work = gg_seed_page( 'ผลงาน', 'portfolio', '<!-- wp:heading --><h2>ผลงาน</h2><!-- /wp:heading --><!-- wp:paragraph --><p>พอร์ตโฟลิโอยังเป็นตัวอย่าง — จะดึงจากทีม Sales &amp; Proposal ภายหลัง</p><!-- /wp:paragraph -->' );
	$blog = gg_seed_page( 'บทความ', 'articles', '<!-- wp:heading --><h2>บทความ</h2><!-- /wp:heading --><!-- wp:paragraph --><p>รวบรวมบทความเก่าจากเว็บจริงในขั้นถัดไป · ตอนนี้เป็นหน้าตัวอย่าง</p><!-- /wp:paragraph -->' );
	$contact = gg_seed_page( 'ติดต่อ', 'contact', '<!-- wp:heading --><h2>ติดต่อ</h2><!-- /wp:heading --><!-- wp:paragraph --><p>hello@growthgame.test · 02-000-0000 · <strong>ตัวอย่าง</strong></p><!-- /wp:paragraph -->' );
	$app = gg_seed_page( 'แอปออกแบบ Gamification', 'app', '<!-- wp:heading --><h2>แอปออกแบบ Gamification</h2><!-- /wp:heading --><!-- wp:paragraph --><p>GoalGapGamify Designer — ช่วยเดิน Goal → Gap → Gamify แล้วได้ canvas หนึ่งหน้า</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>สถานะ: <strong>ตัวอย่างหน้าแนะนำ</strong> · แอปจริงจะอยู่ที่ app.growthgame.co (เฟสถัดไป)</p><!-- /wp:paragraph --><!-- wp:list --><ul><li>กรอก Goal</li><li>หา Gap</li><li>เลือกกลไก Gamify</li><li>ได้ canvas ส่งออก</li></ul><!-- /wp:list -->' );
	$account = gg_seed_page( 'บัญชีของฉัน', 'my-account-gg', '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->' );

	return compact( 'home', 'services', 'courses_page', 'events', 'work', 'blog', 'contact', 'app', 'account' );
}

function gg_seed_create_course( $title, $slug, $content, $price_type = 'free' ) {
	$course_id = wp_insert_post( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => tutor()->course_post_type,
		'post_author'  => 1,
	) );
	if ( is_wp_error( $course_id ) ) {
		return 0;
	}
	update_post_meta( $course_id, '_tutor_course_price_type', $price_type );
	update_post_meta( $course_id, '_tutor_course_level', 'beginner' );
	update_post_meta( $course_id, '_tutor_course_duration', array( 'hours' => '0', 'minutes' => '40', 'seconds' => '0' ) );
	update_post_meta( $course_id, '_tutor_course_benefits', "เข้าใจ Goal–Gap–Gamify\nได้ Gap Card ตัวอย่าง\nพร้อมไปต่อคอร์ส Plus" );
	update_post_meta( $course_id, '_tutor_course_requirements', 'ไม่ต้องมีพื้นฐาน · เป็นเนื้อหาตัวอย่าง' );
	update_post_meta( $course_id, '_tutor_course_target_audience', 'HR · L&D · ผู้นำทีม · นักออกแบบประสบการณ์' );
	update_post_meta( $course_id, '_tutor_is_public_course', 'yes' );
	return $course_id;
}

function gg_seed_add_topic( $course_id, $title, $order = 0 ) {
	return wp_insert_post( array(
		'post_title'  => $title,
		'post_status' => 'publish',
		'post_type'   => 'topics',
		'post_parent' => $course_id,
		'menu_order'  => $order,
		'post_author' => 1,
	) );
}

function gg_seed_add_lesson( $topic_id, $title, $content, $order = 0 ) {
	return wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => tutor()->lesson_post_type,
		'post_parent'  => $topic_id,
		'menu_order'   => $order,
		'post_author'  => 1,
	) );
}

function gg_seed_add_quiz( $topic_id, $title, $questions, $order = 0 ) {
	global $wpdb;
	$quiz_id = wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => 'แบบทดสอบสั้น ๆ (ตัวอย่าง)',
		'post_status'  => 'publish',
		'post_type'    => 'tutor_quiz',
		'post_parent'  => $topic_id,
		'menu_order'   => $order,
		'post_author'  => 1,
	) );
	if ( is_wp_error( $quiz_id ) ) {
		return 0;
	}
	update_post_meta( $quiz_id, 'tutor_quiz_option', array(
		'time_limit'            => array( 'time_value' => 10, 'time_type' => 'minutes' ),
		'hide_quiz_time'        => 0,
		'feedback_mode'         => 'retry',
		'attempts_allowed'      => 3,
		'passing_grade'         => 60,
		'max_questions_for_answer' => 10,
		'question_layout_view'  => 'single_question',
		'questions_order'       => 'rand',
		'hide_question_number_overview' => 0,
		'short_answer_characters_limit' => 200,
	) );

	$q_table = $wpdb->prefix . 'tutor_quiz_questions';
	$a_table = $wpdb->prefix . 'tutor_quiz_question_answers';
	$qi = 0;
	foreach ( $questions as $q ) {
		$qi++;
		$wpdb->insert( $q_table, array(
			'quiz_id'              => $quiz_id,
			'question_title'       => $q['title'],
			'question_description' => '',
			'question_type'        => 'true_false',
			'question_mark'        => 1,
			'question_settings'    => maybe_serialize( array( 'question_type' => 'true_false', 'answer_required' => 1 ) ),
			'question_order'       => $qi,
		) );
		$qid = $wpdb->insert_id;
		$ai = 0;
		foreach ( $q['answers'] as $ans ) {
			$ai++;
			$wpdb->insert( $a_table, array(
				'belongs_question_id'   => $qid,
				'belongs_question_type' => 'true_false',
				'answer_title'          => $ans['title'],
				'is_correct'            => $ans['correct'] ? 1 : 0,
				'answer_order'          => $ai,
			) );
		}
	}
	return $quiz_id;
}

function gg_seed_link_wc_product( $course_id, $price, $name ) {
	$product_id = wp_insert_post( array(
		'post_title'  => $name,
		'post_status' => 'publish',
		'post_type'   => 'product',
		'post_author' => 1,
		'post_content'=> 'สินค้าคอร์ส Tutor LMS (ตัวอย่าง) — ซื้อแล้วปลดล็อกคอร์สอัตโนมัติเมื่อออเดอร์เป็น Processing/Completed',
	) );
	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	update_post_meta( $product_id, '_regular_price', (string) $price );
	update_post_meta( $product_id, '_price', (string) $price );
	update_post_meta( $product_id, '_virtual', 'yes' );
	update_post_meta( $product_id, '_downloadable', 'no' );
	update_post_meta( $product_id, '_sold_individually', 'yes' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_tutor_product', 'yes' );
	update_post_meta( $course_id, '_tutor_course_product_id', $product_id );
	update_post_meta( $course_id, '_tutor_course_price_type', 'paid' );
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $product_id );
	}
	return $product_id;
}

function gg_seed_courses( $media ) {
	$lessons = array(
		array( 'เกมมิฟิเคชันคืออะไร', 'บทนำสั้น ๆ (ตัวอย่าง) — เกมมิฟิเคชันคือการใช้กลไกเกมนอกบริบทเกม เพื่อกระตุ้นพฤติกรรม' ),
		array( 'Goal — ตั้งเป้าหมายให้ชัด', 'ตัวอย่างเนื้อหา: เป้าหมายที่ดีต้องวัดได้ และผูกกับพฤติกรรมที่อยากเห็น' ),
		array( 'Gap — หาช่องว่างของพฤติกรรม', 'ตัวอย่างเนื้อหา: Gap คือระยะห่างระหว่างพฤติกรรมปัจจุบันกับพฤติกรรมเป้าหมาย' ),
		array( 'Gamify — เลือกกลไกให้ตรง Gap', 'ตัวอย่างเนื้อหา: แต้ม แบดจ์ เลเวล เควสต์ — เลือกให้ตรงแรงจูงใจ' ),
		array( 'Gap Card — เครื่องมือหนึ่งหน้า', 'ตัวอย่างเนื้อหา: Gap Card สรุป Goal / Gap / Mechanic / Feedback บนหน้าเดียว' ),
		array( 'สรุปและก้าวถัดไป', 'ตัวอย่างเนื้อหา: จบคอร์สฟรีแล้วอัปเกรด 101 Plus ได้เพื่อรีวิว Gap Card + ใบรับรอง' ),
	);

	$free_id = gg_seed_create_course(
		'[ตัวอย่าง] Gamification 101',
		'gamification-101',
		'คอร์สฟรี 6 คลิปสั้น ๆ (~40 นาที) · รายเดือนมี Q&amp;A · ได้ Gap Card badge (ตัวอย่าง)',
		'free'
	);
	$topic1 = gg_seed_add_topic( $free_id, 'โมดูลหลัก', 1 );
	$i = 0;
	foreach ( $lessons as $L ) {
		$i++;
		gg_seed_add_lesson( $topic1, $L[0], '<!-- wp:paragraph --><p>' . esc_html( $L[1] ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><em>เนื้อหาตัวอย่างสำหรับทดสอบ Tutor LMS</em></p><!-- /wp:paragraph -->', $i );
	}
	gg_seed_add_quiz( $topic1, 'แบบทดสอบสั้น Gamification 101', array(
		array(
			'title'   => 'Goal–Gap–Gamify เป็นกรอบคิดของ Growth Game',
			'answers' => array(
				array( 'title' => 'True', 'correct' => true ),
				array( 'title' => 'False', 'correct' => false ),
			),
		),
		array(
			'title'   => 'Gap Card ใช้สรุปเป้าหมาย ช่องว่าง และกลไกบนหน้าเดียว',
			'answers' => array(
				array( 'title' => 'True', 'correct' => true ),
				array( 'title' => 'False', 'correct' => false ),
			),
		),
	), 100 );

	$plus_id = gg_seed_create_course(
		'[ตัวอย่าง] Gamification 101 Plus',
		'gamification-101-plus',
		'อัปเกรด 990 บาท (ตัวอย่าง) — รีวิว Gap Card + ใบรับรอง + คูปอง 1,000 บาท (รายละเอียดจริงรอ Learning Designer)',
		'paid'
	);
	$topic2 = gg_seed_add_topic( $plus_id, 'โมดูล Plus', 1 );
	gg_seed_add_lesson( $topic2, 'รีวิว Gap Card', '<!-- wp:paragraph --><p>เนื้อหาตัวอย่างสำหรับผู้ที่ซื้อ Plus — อัปโหลด/ส่ง Gap Card เพื่อรีวิว</p><!-- /wp:paragraph -->', 1 );
	gg_seed_add_lesson( $topic2, 'ใบรับรองและของแถม', '<!-- wp:paragraph --><p>ตัวอย่าง: ใบรับรอง + คูปองคอร์สถัดไป</p><!-- /wp:paragraph -->', 2 );
	$plus_product = gg_seed_link_wc_product( $plus_id, 990, '[ตัวอย่าง] คอร์ส 101 Plus — 990 บาท' );

	return array(
		'free'         => $free_id,
		'plus'         => $plus_id,
		'plus_product' => $plus_product,
	);
}

function gg_seed_create_product( $args ) {
	$id = wp_insert_post( array(
		'post_title'   => $args['title'],
		'post_content' => $args['content'],
		'post_excerpt' => $args['excerpt'],
		'post_status'  => 'publish',
		'post_type'    => 'product',
		'post_author'  => 1,
	) );
	wp_set_object_terms( $id, $args['type'], 'product_type' );
	update_post_meta( $id, '_regular_price', (string) $args['price'] );
	update_post_meta( $id, '_price', (string) $args['price'] );
	update_post_meta( $id, '_manage_stock', 'no' );
	update_post_meta( $id, '_stock_status', 'instock' );
	if ( ! empty( $args['virtual'] ) ) {
		update_post_meta( $id, '_virtual', 'yes' );
	} else {
		update_post_meta( $id, '_virtual', 'no' );
		update_post_meta( $id, '_weight', '0.4' );
	}
	if ( ! empty( $args['downloadable'] ) && ! empty( $args['file'] ) ) {
		update_post_meta( $id, '_downloadable', 'yes' );
		update_post_meta( $id, '_download_limit', '-1' );
		update_post_meta( $id, '_download_expiry', '-1' );
		$file = array(
			array(
				'id'   => 'gg-sample-pdf',
				'name' => 'sample-canvas.pdf',
				'file' => $args['file'],
			),
		);
		update_post_meta( $id, '_downloadable_files', $file );
	}
	if ( ! empty( $args['cats'] ) ) {
		wp_set_object_terms( $id, $args['cats'], 'product_cat' );
	}
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( $id );
	}
	return $id;
}

function gg_seed_shop_products( $media ) {
	// Ensure product categories.
	$cats = array();
	foreach ( array( 'books' => 'หนังสือ', 'cards' => 'การ์ดและชุดเครื่องมือ', 'digital' => 'ไฟล์ดิจิทัล', 'bundle' => 'ชุดสุดคุ้ม' ) as $slug => $name ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
		$cats[ $slug ] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
	}

	$pdf_url = '';
	if ( ! empty( $media['pdf'] ) ) {
		$pdf_url = '/wp-content/uploads/gg-seed/sample-canvas.pdf';
	}
	if ( ! $pdf_url ) {
		$upload = wp_upload_dir();
		$pdf_url = '/wp-content/uploads/gg-seed/sample-canvas.pdf';
	}

	$book1 = gg_seed_create_product( array(
		'title'   => '[ตัวอย่าง] เกมมิฟิเคชัน จูงใจคนด้วยกลไกเกม',
		'content' => 'หนังสือเล่มแรก · พื้นฐานเกมมิฟิเคชันสำหรับองค์กรและทีม · ราคาตัวอย่าง',
		'excerpt' => 'หนังสือเล่ม 1 (ตัวอย่าง)',
		'price'   => 450,
		'type'    => 'simple',
		'cats'    => array( (int) $cats['books'] ),
	) );
	$book2 = gg_seed_create_product( array(
		'title'   => '[ตัวอย่าง] เกมมิฟิเคชัน 2 Goal–Gap–Gamify',
		'content' => 'หนังสือเล่มสอง · กรอบ Goal–Gap–Gamify · ราคาตัวอย่าง',
		'excerpt' => 'หนังสือเล่ม 2 (ตัวอย่าง)',
		'price'   => 550,
		'type'    => 'simple',
		'cats'    => array( (int) $cats['books'] ),
	) );
	$cards = gg_seed_create_product( array(
		'title'   => '[ตัวอย่าง] ชุดการ์ด Gap Card (ส่งถึงบ้าน)',
		'content' => 'ชุดการ์ดกายภาพสำหรับเวิร์กช็อป · ราคาตัวอย่าง',
		'excerpt' => 'การ์ดกายภาพ (ตัวอย่าง)',
		'price'   => 790,
		'type'    => 'simple',
		'cats'    => array( (int) $cats['cards'] ),
	) );
	$digital = gg_seed_create_product( array(
		'title'        => '[ตัวอย่าง] Canvas Template (ไฟล์ PDF)',
		'content'      => 'ไฟล์ดิจิทัลดาวน์โหลดทันทีหลังชำระ (โหมดทดสอบ) · ไฟล์ตัวอย่าง PDF',
		'excerpt'      => 'ดาวน์โหลด PDF (ตัวอย่าง)',
		'price'        => 190,
		'type'         => 'simple',
		'virtual'      => true,
		'downloadable' => true,
		'file'         => $pdf_url,
		'cats'         => array( (int) $cats['digital'] ),
	) );

	// Bundle as simple for demo compatibility (grouped/bundle plugins not free/required).
	$bundle = gg_seed_create_product( array(
		'title'   => '[ตัวอย่าง] ชุดสุดคุ้ม หนังสือ 2 เล่ม + Canvas',
		'content' => 'แพ็กตัวอย่างรวมหนังสือ 2 เล่ม + สิทธิ์ไฟล์ Canvas (ในเดโมนี้เป็นสินค้าเดี่ยวราคาพิเศษ)',
		'excerpt' => 'ชุดสุดคุ้ม (ตัวอย่าง)',
		'price'   => 990,
		'type'    => 'simple',
		'cats'    => array( (int) $cats['bundle'] ),
	) );

	// Dedicated shop page content pointing at products shortcode.
	$shop_page_id = wc_get_page_id( 'shop' );
	if ( $shop_page_id && $shop_page_id > 0 ) {
		wp_update_post( array(
			'ID'           => $shop_page_id,
			'post_title'   => 'ร้านค้า',
			'post_name'    => 'shop',
			'post_content' => '<!-- wp:paragraph --><p>หนังสือ สินค้าส่งถึงบ้าน และไฟล์ดิจิทัล · ราคาและปกเป็นตัวอย่าง</p><!-- /wp:paragraph -->',
		) );
	}

	return compact( 'book1', 'book2', 'cards', 'digital', 'bundle' );
}

function gg_seed_menus_and_front( $pages ) {
	$menu_name = 'Growth Game Main';
	$menu_id   = wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) {
		$menus = wp_get_nav_menus();
		foreach ( $menus as $m ) {
			if ( $m->name === $menu_name ) {
				$menu_id = $m->term_id;
				break;
			}
		}
	}

	$items = array(
		array( 'หน้าแรก', $pages['home'] ),
		array( 'บริการ', $pages['services'] ),
		array( 'คอร์สเรียน', $pages['courses_page'] ),
		array( 'กิจกรรม', $pages['events'] ),
		array( 'ผลงาน', $pages['work'] ),
		array( 'บทความ', $pages['blog'] ),
		array( 'ติดต่อ', $pages['contact'] ),
		array( 'บัญชีของฉัน', $pages['account'] ),
	);
	// Clear existing items loosely by recreating links.
	foreach ( $items as $it ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $it[0],
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $it[1],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}

	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	// TT5 uses 'primary' sometimes via wp_navigation; also set classic locations.
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	// Also create a wp_navigation post for block themes.
	$nav_content = '';
	foreach ( $items as $it ) {
		$url = get_permalink( $it[1] );
		$nav_content .= '<!-- wp:navigation-link {"label":"' . esc_attr( $it[0] ) . '","type":"page","id":' . (int) $it[1] . ',"url":"' . esc_url( $url ) . '","kind":"post-type"} /-->';
	}
	$nav_id = wp_insert_post( array(
		'post_title'   => 'Growth Game Main',
		'post_status'  => 'publish',
		'post_type'    => 'wp_navigation',
		'post_content' => $nav_content,
	) );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $pages['home'] );
	update_option( 'blogdescription', 'สำเนาทดสอบ — ไม่ใช่เว็บจริง growthgame.co' );

	// Permalinks.
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();
}

function gg_seed_gamipress() {
	if ( ! function_exists( 'gamipress_get_points_types' ) && ! post_type_exists( 'points-type' ) ) {
		return;
	}
	// Create a simple points type + badge if GamiPress CPTs exist.
	if ( post_type_exists( 'points-type' ) ) {
		$pts = get_page_by_path( 'gg-points', OBJECT, 'points-type' );
		if ( ! $pts ) {
			wp_insert_post( array(
				'post_title'  => 'แต้ม Growth Game (ตัวอย่าง)',
				'post_name'   => 'gg-points',
				'post_status' => 'publish',
				'post_type'   => 'points-type',
			) );
		}
	}
	if ( post_type_exists( 'achievement-type' ) ) {
		$at = get_page_by_path( 'badges', OBJECT, 'achievement-type' );
		if ( ! $at ) {
			wp_insert_post( array(
				'post_title'  => 'แบดจ์',
				'post_name'   => 'badges',
				'post_status' => 'publish',
				'post_type'   => 'achievement-type',
			) );
		}
	}
	if ( post_type_exists( 'badges' ) ) {
		$badge = get_page_by_path( 'gap-card-badge', OBJECT, 'badges' );
		if ( ! $badge ) {
			wp_insert_post( array(
				'post_title'   => '[ตัวอย่าง] Gap Card Badge',
				'post_name'    => 'gap-card-badge',
				'post_content' => 'แบดจ์ตัวอย่างเมื่อจบ Gamification 101',
				'post_status'  => 'publish',
				'post_type'    => 'badges',
			) );
		}
	}
}

// WP-CLI / Playground helper.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'gg-seed', function () {
		delete_option( GG_SEED_OPTION );
		gg_seed_run();
		update_option( GG_SEED_OPTION, GG_SEED_VERSION );
		WP_CLI::success( 'Growth Game seed complete' );
	} );
}
