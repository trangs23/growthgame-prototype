<?php
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'growthgame-fonts',
		'https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'growthgame-child',
		get_stylesheet_uri(),
		array( 'growthgame-fonts' ),
		'1.0.0'
	);
	$css = ':root{--gg-deep:#132042;--gg-light:#8FD0EA;--gg-gray:#ECECEE;--gg-lilac:#5D68D8;--gg-violet:#343273;--gg-yellow:#DDD94A;}body{font-family:Kanit,Poppins,system-ui,sans-serif;}a{color:var(--gg-lilac)}.wp-site-blocks{font-family:Kanit,Poppins,system-ui,sans-serif}.gg-banner{background:#DDD94A;color:#132042;text-align:center;padding:8px 12px;font-size:14px;font-weight:500}';
	wp_add_inline_style( 'growthgame-child', $css );
} );

add_action( 'wp_body_open', function () {
	echo '<div class="gg-banner">สำเนาทดสอบ WordPress · ป้าย “ตัวอย่าง” = ข้อมูลสมมติ · ไม่ใช่เว็บจริง growthgame.co · ชำระเงินโหมดทดสอบเท่านั้น</div>';
} );
