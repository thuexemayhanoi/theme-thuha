<?php
/**
 * Vietnamese admin screen for non-developers: status, preflight, seeding,
 * pairing, content audit, ON/OFF/undo. Every action requires a nonce and
 * the manage_options capability (edit_post for the editor meta box).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI.
 */
class RBHVI_Admin {

	const SLUG = 'rbhvi';

	/**
	 * Register hooks.
	 */
	public static function boot() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		foreach ( array( 'enable', 'disable', 'undo', 'seed', 'pair', 'unpair', 'confirm_suggestions', 'settings' ) as $action ) {
			add_action( 'admin_post_rbhvi_' . $action, array( __CLASS__, 'handle_' . $action ) );
		}
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 5, 2 );
		foreach ( array( 'post', 'page' ) as $type ) {
			add_filter( "manage_{$type}_posts_columns", array( __CLASS__, 'columns' ) );
			add_action( "manage_{$type}_posts_custom_column", array( __CLASS__, 'column' ), 10, 2 );
		}
		add_action( 'category_add_form_fields', array( __CLASS__, 'term_field_add' ) );
		add_action( 'category_edit_form_fields', array( __CLASS__, 'term_field_edit' ) );
		// created_term/edited_term run before Yoast rebuilds the term (PHP_INT_MAX), so its stored URL is right.
		add_action( 'created_term', array( __CLASS__, 'save_term' ), 10, 3 );
		add_action( 'edited_term', array( __CLASS__, 'save_term' ), 10, 3 );
		add_action( 'admin_notices', array( __CLASS__, 'post_notice' ) );
	}

	/**
	 * Menu entry.
	 */
	public static function menu() {
		add_menu_page( 'Tiếng Việt (/vi/)', 'Tiếng Việt /vi/', 'manage_options', self::SLUG, array( __CLASS__, 'page' ), 'dashicons-translation', 58 );
	}

	/**
	 * Admin URL of the screen.
	 *
	 * @param array $args Query args.
	 * @return string
	 */
	private static function url( $args = array() ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . self::SLUG ) );
	}

	/**
	 * Common guard for admin-post actions.
	 *
	 * @param string $action Action.
	 */
	private static function guard( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html( 'Bạn không có quyền thực hiện thao tác này. Hãy đăng nhập bằng tài khoản Quản trị viên.' ), 403 );
		}
		check_admin_referer( 'rbhvi_' . $action );
	}

	/**
	 * Store a notice for the next screen load.
	 *
	 * @param string $type    success|error|warning|info.
	 * @param string $message Message.
	 * @param array  $details Optional rows (checks or seed report).
	 */
	private static function notice( $type, $message, $details = array() ) {
		set_transient(
			'rbhvi_notice_' . get_current_user_id(),
			array(
				'type'    => $type,
				'message' => $message,
				'details' => $details,
			),
			300
		);
	}

	/**
	 * Redirect back to the screen.
	 */
	private static function back() {
		wp_safe_redirect( self::url() );
		exit;
	}

	/**
	 * Enable /vi/.
	 */
	public static function handle_enable() {
		self::guard( 'enable' );
		$result = RBHVI_State::enable();
		if ( $result['ok'] ) {
			self::notice( 'success', 'Đã bật phần tiếng Việt /vi/. Hãy mở /vi/ và một trang tiếng Anh để kiểm tra, rồi xóa bộ nhớ đệm (cache) nếu website có dùng.', $result['checks'] );
		} elseif ( $result['verify'] ) {
			self::notice( 'error', 'Đã thử bật nhưng kiểm tra sau khi bật không đạt, nên plugin đã tự quay về trạng thái trước. Không có nội dung nào bị thay đổi.', array_merge( $result['checks'], $result['verify'] ) );
		} else {
			self::notice( 'error', 'Chưa bật /vi/ vì phần kiểm tra trước khi bật phát hiện lỗi. Xem các dòng màu đỏ bên dưới, xử lý rồi thử lại.', $result['checks'] );
		}
		self::back();
	}

	/**
	 * Disable /vi/.
	 */
	public static function handle_disable() {
		self::guard( 'disable' );
		RBHVI_State::disable();
		self::notice( 'success', 'Đã tắt /vi/. Phần tiếng Anh vẫn hoạt động bình thường; trang Việt không bị xóa, chỉ tạm trả 404 và không có trong sitemap.' );
		self::back();
	}

	/**
	 * Undo last transition.
	 */
	public static function handle_undo() {
		self::guard( 'undo' );
		$result   = RBHVI_State::undo();
		$messages = array(
			'no_history'    => array( 'info', 'Chưa có lần chuyển trạng thái nào để hoàn tác.' ),
			'undo_enabled'  => array( 'success', 'Đã hoàn tác: /vi/ được bật lại.' ),
			'undo_disabled' => array( 'success', 'Đã hoàn tác: /vi/ đã tắt như trước.' ),
			'undo_failed'   => array( 'error', 'Không thể bật lại /vi/ khi hoàn tác vì kiểm tra không đạt. Trạng thái vẫn là TẮT.' ),
		);
		$message  = $messages[ $result['message'] ];
		$details  = isset( $result['result']['checks'] ) ? array_merge( $result['result']['checks'], $result['result']['verify'] ) : array();
		self::notice( $message[0], $message[1], $details );
		self::back();
	}

	/**
	 * Create the draft page set.
	 */
	public static function handle_seed() {
		self::guard( 'seed' );
		$report = RBHVI_Seeder::run();
		$counts = array_count_values( wp_list_pluck( $report, 'status' ) );
		$type   = isset( $counts['conflict'] ) || isset( $counts['error'] ) ? 'warning' : 'success';
		self::notice( $type, sprintf( 'Đã xử lý bộ trang Việt: %d trang mới (bản nháp), %d trang đã có, %d xung đột. Không có trang nào được xuất bản tự động.', isset( $counts['created'] ) ? $counts['created'] : 0, ( isset( $counts['exists'] ) ? $counts['exists'] : 0 ) + ( isset( $counts['adopted'] ) ? $counts['adopted'] : 0 ), isset( $counts['conflict'] ) ? $counts['conflict'] : 0 ), $report );
		self::back();
	}

	/**
	 * Validate a VI ↔ EN pair. Returns an error message or ''.
	 *
	 * @param int $vi_id VI post ID.
	 * @param int $en_id EN post ID.
	 * @return string
	 */
	public static function pair_error( $vi_id, $en_id ) {
		if ( ! $vi_id || ! get_post( $vi_id ) || ! RBHVI_Language::is_vi( $vi_id ) ) {
			return 'Trang/bài tiếng Việt không hợp lệ. Hãy chọn một trang/bài đã đánh dấu là tiếng Việt.';
		}
		$en = get_post( $en_id );
		if ( ! $en || ! in_array( $en->post_type, RBHVI_Language::POST_TYPES, true ) || 'trash' === $en->post_status ) {
			return 'Không tìm thấy trang/bài tiếng Anh với ID ' . (int) $en_id . '.';
		}
		if ( RBHVI_Language::is_vi( $en_id ) ) {
			return 'Mục được chọn ở cột tiếng Anh lại là nội dung tiếng Việt.';
		}
		foreach ( RBHVI_Language::vi_ids_paired_with( $en_id ) as $other ) {
			if ( (int) $other !== (int) $vi_id ) {
				return 'Trang tiếng Anh "' . $en->post_title . '" đã được ghép với trang Việt "' . get_the_title( $other ) . '" (ID ' . (int) $other . '). Mỗi trang chỉ ghép với một trang.';
			}
		}
		return '';
	}

	/**
	 * Save a pair.
	 */
	public static function handle_pair() {
		self::guard( 'pair' );
		$vi_id = isset( $_POST['vi_id'] ) ? absint( $_POST['vi_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::guard().
		$en_id = isset( $_POST['en_id'] ) ? absint( $_POST['en_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::guard().
		$error = self::pair_error( $vi_id, $en_id );
		if ( $error ) {
			self::notice( 'error', $error );
		} else {
			update_post_meta( $vi_id, RBHVI_Language::META_PAIR, $en_id );
			RBHVI_State::clear_seo_caches();
			self::notice( 'success', 'Đã ghép "' . get_the_title( $vi_id ) . '" ↔ "' . get_the_title( $en_id ) . '". Hreflang chỉ xuất hiện khi cả hai trang đã xuất bản và /vi/ đang bật.' );
		}
		self::back();
	}

	/**
	 * Remove a pair (stored on the Vietnamese side only).
	 */
	public static function handle_unpair() {
		self::guard( 'unpair' );
		$vi_id = isset( $_POST['vi_id'] ) ? absint( $_POST['vi_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::guard().
		if ( $vi_id && RBHVI_Language::is_vi( $vi_id ) ) {
			delete_post_meta( $vi_id, RBHVI_Language::META_PAIR );
			RBHVI_State::clear_seo_caches();
			self::notice( 'success', 'Đã bỏ ghép cặp cho "' . get_the_title( $vi_id ) . '".' );
		} else {
			self::notice( 'error', 'Không tìm thấy trang tiếng Việt cần bỏ ghép.' );
		}
		self::back();
	}

	/**
	 * Confirm all suggested pairs that are still valid.
	 */
	public static function handle_confirm_suggestions() {
		self::guard( 'confirm_suggestions' );
		$done   = 0;
		$errors = array();
		foreach ( RBHVI_Language::vi_ids() as $vi_id ) {
			$suggested = (int) get_post_meta( $vi_id, RBHVI_Language::META_SUGG, true );
			if ( ! $suggested || get_post_meta( $vi_id, RBHVI_Language::META_PAIR, true ) ) {
				continue;
			}
			$error = self::pair_error( $vi_id, $suggested );
			if ( $error ) {
				$errors[] = array(
					'level'   => 'error',
					'message' => get_the_title( $vi_id ) . ': ' . $error,
				);
				continue;
			}
			update_post_meta( $vi_id, RBHVI_Language::META_PAIR, $suggested );
			++$done;
		}
		RBHVI_State::clear_seo_caches();
		self::notice( $errors ? 'warning' : 'success', sprintf( 'Đã xác nhận %d cặp đề xuất.', $done ), $errors );
		self::back();
	}

	/**
	 * Save settings.
	 */
	public static function handle_settings() {
		self::guard( 'settings' );
		RBHVI_State::save_settings(
			array(
				'sitemap_pairs_only' => ! empty( $_POST['sitemap_pairs_only'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::guard().
				'home_layout'        => isset( $_POST['home_layout'] ) ? sanitize_key( wp_unslash( $_POST['home_layout'] ) ) : 'landing', // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::guard().
			)
		);
		self::notice( 'success', 'Đã lưu cài đặt.' );
		self::back();
	}

	/**
	 * Audit issues of one Vietnamese post.
	 *
	 * @param WP_Post $post Post.
	 * @return string[]
	 */
	public static function issues( $post ) {
		$issues = array();
		$title  = (string) get_post_meta( $post->ID, '_yoast_wpseo_title', true ) . (string) get_post_meta( $post->ID, RBHVI_Language::META_TITLE, true );
		$desc   = (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) . (string) get_post_meta( $post->ID, RBHVI_Language::META_DESC, true );
		if ( '' === trim( $title ) ) {
			$issues[] = 'Thiếu tiêu đề SEO';
		}
		if ( '' === trim( $desc ) ) {
			$issues[] = 'Thiếu mô tả SEO';
		}
		if ( '' === trim( $post->post_title ) ) {
			$issues[] = 'Thiếu tiêu đề trang (H1)';
		}
		if ( preg_match( '/<h1[\s>]/i', $post->post_content ) ) {
			$issues[] = 'Nội dung có thêm H1 (mỗi trang chỉ một H1 là tiêu đề)';
		}
		$key = (string) get_post_meta( $post->ID, RBHVI_Language::META_KEY, true );
		$def = $key ? RBHVI_Config::page( $key ) : null;
		if ( ! get_post_meta( $post->ID, RBHVI_Language::META_PAIR, true ) && ! ( $def && null === $def['en'] ) ) {
			$issues[] = get_post_meta( $post->ID, RBHVI_Language::META_SUGG, true ) ? 'Chưa xác nhận cặp tiếng Anh (có đề xuất)' : 'Chưa ghép cặp tiếng Anh';
		} elseif ( get_post_meta( $post->ID, RBHVI_Language::META_PAIR, true ) && 'publish' === $post->post_status && ! RBHVI_Language::valid_en_for( $post->ID ) ) {
			$issues[] = 'Cặp tiếng Anh không hợp lệ (chưa xuất bản, trùng cặp hoặc không phải tiếng Anh)';
		}
		if ( 'page' === $post->post_type && ! RBHVI_Language::path_is_vi( get_page_uri( $post ) ) ) {
			$issues[] = 'Nằm ngoài /vi/ (đặt trang cha là trang chủ tiếng Việt)';
		}
		if ( $def && ! empty( $def['review'] ) ) {
			$issues[] = 'Trang pháp lý: chủ website cần đọc và duyệt trước khi xuất bản';
		}
		if ( 'publish' === $post->post_status ) {
			foreach ( RBHVI_Config::pages() as $other => $unused ) {
				$id = RBHVI_State::page_id( $other );
				if ( $id && 'publish' !== get_post_status( $id ) && false !== strpos( $post->post_content, RBHVI_Config::page_url( $other ) ) ) {
					$issues[] = 'Liên kết tới trang chưa xuất bản: ' . RBHVI_Config::page_url( $other );
				}
			}
		}
		return $issues;
	}

	/**
	 * Render the admin screen.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$notice = get_transient( 'rbhvi_notice_' . get_current_user_id() );
		delete_transient( 'rbhvi_notice_' . get_current_user_id() );
		$enabled = RBHVI_State::is_enabled();
		$checks  = RBHVI_Preflight::run();
		$history = RBHVI_State::history();
		echo '<div class="wrap rbhvi-admin"><h1>Phần tiếng Việt (/vi/)</h1>';
		echo '<style>.rbhvi-admin .card{max-width:1100px}.rbhvi-ok{color:#007017}.rbhvi-warning{color:#996800}.rbhvi-error{color:#b32d2e;font-weight:600}.rbhvi-badge{display:inline-block;padding:3px 10px;border-radius:12px;color:#fff;font-weight:600}.rbhvi-on{background:#007017}.rbhvi-off{background:#646970}.rbhvi-admin form.inline{display:inline-block;margin:0 8px 8px 0}.rbhvi-admin td ul{margin:0;padding-left:16px;list-style:disc}</style>';
		if ( $notice ) {
			echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . '"><p>' . esc_html( $notice['message'] ) . '</p>';
			self::detail_list( $notice['details'] );
			echo '</div>';
		}
		echo '<p>Tiếng Anh giữ nguyên ở địa chỉ gốc; tiếng Việt nằm dưới <code>' . esc_html( home_url( '/vi/' ) ) . '</code>. Nội dung Việt được viết riêng, không dịch máy. Plugin không tự xuất bản và không sửa nội dung tiếng Anh.</p>';

		// 1. Status.
		echo '<div class="card"><h2>1. Trạng thái</h2><p>Phần tiếng Việt đang: ' . ( $enabled ? '<span class="rbhvi-badge rbhvi-on">BẬT</span>' : '<span class="rbhvi-badge rbhvi-off">TẮT</span>' ) . '</p>';
		echo '<p>Khi TẮT: mọi địa chỉ /vi/ trả lỗi 404, không có trong sitemap, nút VI | EN ẩn. Trang Việt vẫn được giữ nguyên.</p>';
		self::button( 'enable', 'Bật /vi/ (có kiểm tra trước)', 'primary', $enabled );
		self::button( 'disable', 'Tắt /vi/', 'secondary', ! $enabled );
		if ( $history ) {
			$last = end( $history );
			self::button( 'undo', 'Hoàn tác lần chuyển gần nhất (' . ( $last['to'] ? 'đã bật' : 'đã tắt' ) . ' lúc ' . wp_date( 'd/m/Y H:i', $last['time'] ) . ')', 'secondary' );
		}
		echo '<h3>Kiểm tra trước khi bật (chỉ đọc, không thay đổi gì)</h3>';
		self::detail_list( $checks );
		echo '</div>';

		// 2. Managed page set.
		echo '<div class="card"><h2>2. Bộ trang tiếng Việt</h2><p>Bấm nút để tạo các trang còn thiếu dưới dạng <strong>bản nháp</strong>. Chạy lại an toàn: trang đã có được giữ nguyên, không tạo trùng. Hãy đọc, chỉnh sửa và tự xuất bản từng trang (nên xuất bản đồng thời để liên kết nội bộ không bị 404).</p>';
		self::button( 'seed', 'Tạo bộ trang Việt (bản nháp)', 'primary' );
		self::button( 'confirm_suggestions', 'Xác nhận các cặp Anh–Việt được đề xuất', 'secondary' );
		echo '<table class="widefat striped"><thead><tr><th>Trang</th><th>Địa chỉ</th><th>Trạng thái</th><th>Cặp tiếng Anh</th></tr></thead><tbody>';
		foreach ( RBHVI_Config::pages() as $key => $def ) {
			$id = RBHVI_State::page_id( $key );
			echo '<tr><td>' . ( $id ? '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>' : esc_html( RBHVI_Config::render( $def['title'] ) ) ) . '</td>';
			echo '<td><code>/' . esc_html( RBHVI_Config::page_path( $key ) ) . '/</code></td>';
			echo '<td>' . esc_html( $id ? self::status_label( get_post_status( $id ) ) : 'Chưa tạo' ) . '</td><td>';
			echo $id ? wp_kses_post( self::pair_cell( $id ) ) : '—';
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';

		// 3. Pairing.
		$vi_posts = get_posts(
			array(
				'post_type'      => RBHVI_Language::POST_TYPES,
				'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
				'posts_per_page' => 500, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- admin screen, bounded.
				'orderby'        => 'title',
				'order'          => 'ASC',
				'rbhvi_skip'     => true,
				'post__in'       => RBHVI_Language::vi_ids() ? RBHVI_Language::vi_ids() : array( 0 ),
			)
		);
		$en_posts = get_posts(
			array(
				'post_type'      => RBHVI_Language::POST_TYPES,
				'post_status'    => 'publish',
				'posts_per_page' => 1000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- admin pairing list, bounded.
				'orderby'        => 'title',
				'order'          => 'ASC',
				'rbhvi_skip'     => true,
				'post__not_in'   => RBHVI_Language::vi_ids(), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			)
		);
		echo '<div class="card"><h2>3. Ghép cặp trang/bài Anh–Việt</h2><p>Chỉ ghép hai trang thật sự cùng nội dung/mục đích. Cặp được lưu ở phía tiếng Việt; trang tiếng Anh không bị chỉnh sửa. Hreflang (en, vi, x-default → tiếng Anh) chỉ in khi cả hai trang đã xuất bản.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rbhvi_pair">';
		wp_nonce_field( 'rbhvi_pair' );
		echo '<label>Tiếng Việt: <select name="vi_id" required><option value="">— chọn —</option>';
		foreach ( $vi_posts as $post ) {
			echo '<option value="' . (int) $post->ID . '">' . esc_html( $post->post_title . ' (#' . $post->ID . ', ' . self::status_label( $post->post_status ) . ')' ) . '</option>';
		}
		echo '</select></label> ↔ <label>Tiếng Anh: <select name="en_id" required><option value="">— chọn —</option>';
		foreach ( $en_posts as $post ) {
			echo '<option value="' . (int) $post->ID . '">' . esc_html( ( 'page' === $post->post_type ? 'Trang: ' : 'Bài: ' ) . $post->post_title . ' (#' . $post->ID . ')' ) . '</option>';
		}
		echo '</select></label> ';
		submit_button( 'Lưu cặp', 'primary', 'submit', false );
		echo '</form>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:12px"><input type="hidden" name="action" value="rbhvi_unpair">';
		wp_nonce_field( 'rbhvi_unpair' );
		echo '<label>Bỏ ghép: <select name="vi_id" required><option value="">— chọn trang Việt —</option>';
		foreach ( $vi_posts as $post ) {
			if ( get_post_meta( $post->ID, RBHVI_Language::META_PAIR, true ) ) {
				echo '<option value="' . (int) $post->ID . '">' . esc_html( $post->post_title ) . '</option>';
			}
		}
		echo '</select></label> ';
		submit_button( 'Bỏ ghép', 'secondary', 'submit', false );
		echo '</form></div>';

		// 4. Audit.
		echo '<div class="card"><h2>4. Kiểm tra nội dung tiếng Việt</h2><table class="widefat striped"><thead><tr><th>Trang/bài</th><th>Loại</th><th>Trạng thái</th><th>Cần xử lý</th></tr></thead><tbody>';
		if ( ! $vi_posts ) {
			echo '<tr><td colspan="4">Chưa có trang/bài tiếng Việt nào.</td></tr>';
		}
		foreach ( $vi_posts as $post ) {
			$issues = self::issues( $post );
			echo '<tr><td><a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html( $post->post_title ? $post->post_title : '(không tiêu đề)' ) . '</a></td><td>' . esc_html( 'page' === $post->post_type ? 'Trang' : 'Bài viết' ) . '</td><td>' . esc_html( self::status_label( $post->post_status ) ) . '</td><td>';
			echo $issues ? '<ul><li>' . implode( '</li><li>', array_map( 'esc_html', $issues ) ) . '</li></ul>' : '<span class="rbhvi-ok">Đủ thông tin</span>';
			echo '</td></tr>';
		}
		echo '</tbody></table><p>Để thêm bài viết tiếng Việt: tạo bài mới, chọn “Tiếng Việt” trong hộp <em>Ngôn ngữ</em> ở cột phải và (nếu có) nhập ID bài tiếng Anh tương ứng. Bài sẽ có địa chỉ /vi/ten-bai/.</p></div>';

		// 5. Settings and history.
		$settings = RBHVI_State::settings();
		echo '<div class="card"><h2>5. Cài đặt</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rbhvi_settings">';
		wp_nonce_field( 'rbhvi_settings' );
		echo '<p><label><input type="checkbox" name="sitemap_pairs_only" value="1" ' . checked( $settings['sitemap_pairs_only'], true, false ) . '> Chỉ đưa bài viết tiếng Việt <strong>đã ghép cặp</strong> vào sitemap (trang tĩnh /vi/ đã xuất bản luôn có trong sitemap).</label></p>';
		echo '<p><strong>Bố cục trang chủ /vi/:</strong><br><label><input type="radio" name="home_layout" value="landing" ' . checked( $settings['home_layout'], 'landing', false ) . '> Giống trang chủ tiếng Anh (ảnh lớn, các loại xe, bảng giá, các bước, hỏi đáp; nội dung trang đang lưu nằm trong mục “Toàn bộ chi tiết”)</label><br><label><input type="radio" name="home_layout" value="page" ' . checked( $settings['home_layout'], 'page', false ) . '> Trang thường (chỉ hiển thị nội dung trang như bản 1.0.0)</label></p><p>';
		submit_button( 'Lưu', 'secondary', 'submit', false );
		echo '</p></form></div>';
		echo '<div class="card"><h2>6. Lịch sử bật/tắt</h2>';
		if ( ! $history ) {
			echo '<p>Chưa có.</p>';
		} else {
			echo '<ul>';
			foreach ( array_reverse( $history ) as $row ) {
				$user = get_userdata( $row['user'] );
				echo '<li>' . esc_html( wp_date( 'd/m/Y H:i', $row['time'] ) . ' — ' . ( $row['to'] ? 'BẬT' : 'TẮT' ) . ' (' . $row['action'] . ')' . ( $user ? ' bởi ' . $user->display_name : '' ) . ( $row['note'] ? ' — ' . $row['note'] : '' ) ) . '</li>';
			}
			echo '</ul>';
		}
		echo '</div></div>';
	}

	/**
	 * Action button form.
	 *
	 * @param string $action   Action.
	 * @param string $label    Label.
	 * @param string $css      Button class.
	 * @param bool   $disabled Disabled.
	 */
	private static function button( $action, $label, $css, $disabled = false ) {
		echo '<form class="inline" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="rbhvi_' . esc_attr( $action ) . '">';
		wp_nonce_field( 'rbhvi_' . $action );
		submit_button( $label, $css, 'submit', false, $disabled ? array( 'disabled' => 'disabled' ) : array() );
		echo '</form>';
	}

	/**
	 * Check/report list.
	 *
	 * @param array $rows Rows.
	 */
	private static function detail_list( $rows ) {
		if ( ! $rows ) {
			return;
		}
		$icons = array(
			'ok'       => '✔',
			'warning'  => '⚠',
			'error'    => '✖',
			'created'  => '✔',
			'exists'   => '•',
			'adopted'  => '•',
			'conflict' => '⚠',
		);
		echo '<ul>';
		foreach ( $rows as $key => $row ) {
			$level = isset( $row['level'] ) ? $row['level'] : ( isset( $row['status'] ) ? $row['status'] : 'ok' );
			$class = in_array( $level, array( 'error', 'conflict' ), true ) ? 'rbhvi-error' : ( 'warning' === $level ? 'rbhvi-warning' : 'rbhvi-ok' );
			$label = is_string( $key ) && RBHVI_Config::page( $key ) ? RBHVI_Config::render( RBHVI_Config::page( $key )['title'] ) . ': ' : '';
			echo '<li class="' . esc_attr( $class ) . '">' . esc_html( ( isset( $icons[ $level ] ) ? $icons[ $level ] : '•' ) . ' ' . $label . $row['message'] ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Vietnamese post status label.
	 *
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$labels = array(
			'publish' => 'Đã xuất bản',
			'draft'   => 'Bản nháp',
			'pending' => 'Chờ duyệt',
			'future'  => 'Hẹn giờ',
			'private' => 'Riêng tư',
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : (string) $status;
	}

	/**
	 * Pair description for a Vietnamese post.
	 *
	 * @param int $vi_id Post ID.
	 * @return string HTML.
	 */
	private static function pair_cell( $vi_id ) {
		$pair = (int) get_post_meta( $vi_id, RBHVI_Language::META_PAIR, true );
		if ( $pair ) {
			$valid = RBHVI_Language::valid_en_for( $vi_id ) ? ' <span class="rbhvi-ok">(hợp lệ, có hreflang khi bật)</span>' : ' <span class="rbhvi-warning">(chưa đủ điều kiện hreflang)</span>';
			return '<a href="' . esc_url( get_permalink( $pair ) ) . '">' . esc_html( get_the_title( $pair ) ) . '</a>' . $valid;
		}
		$sugg = (int) get_post_meta( $vi_id, RBHVI_Language::META_SUGG, true );
		return $sugg ? 'Đề xuất: <a href="' . esc_url( get_permalink( $sugg ) ) . '">' . esc_html( get_the_title( $sugg ) ) . '</a> (chưa xác nhận)' : 'Không có';
	}

	/**
	 * Editor meta box.
	 */
	public static function meta_box() {
		foreach ( RBHVI_Language::POST_TYPES as $type ) {
			add_meta_box( 'rbhvi_language', 'Ngôn ngữ (Anh / Việt)', array( __CLASS__, 'render_meta_box' ), $type, 'side' );
		}
	}

	/**
	 * Meta box UI.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'rbhvi_meta_' . $post->ID, 'rbhvi_meta_nonce' );
		$is_vi = RBHVI_Language::is_vi( $post );
		$pair  = (int) get_post_meta( $post->ID, RBHVI_Language::META_PAIR, true );
		echo '<p><label for="rbhvi_lang">Ngôn ngữ của nội dung:</label><br><select id="rbhvi_lang" name="rbhvi_lang"><option value="en"' . selected( ! $is_vi, true, false ) . '>Tiếng Anh (địa chỉ gốc)</option><option value="vi"' . selected( $is_vi, true, false ) . '>Tiếng Việt (/vi/)</option></select></p>';
		echo '<p><label for="rbhvi_pair">ID trang/bài tiếng Anh tương ứng (chỉ cho nội dung tiếng Việt):</label><br><input type="number" min="0" id="rbhvi_pair" name="rbhvi_pair" value="' . ( $pair ? (int) $pair : '' ) . '" class="small-text"></p>';
		if ( $is_vi && $pair ) {
			echo '<p>Đang ghép với: <a href="' . esc_url( get_permalink( $pair ) ) . '">' . esc_html( get_the_title( $pair ) ) . '</a></p>';
		} elseif ( ! $is_vi ) {
			$vi = RBHVI_Language::vi_ids_paired_with( $post->ID );
			echo $vi ? '<p>Bản tiếng Việt: <a href="' . esc_url( get_edit_post_link( $vi[0] ) ) . '">' . esc_html( get_the_title( $vi[0] ) ) . '</a></p>' : '<p>Chưa có bản tiếng Việt ghép cặp.</p>';
		}
		echo '<p class="description">Viết riêng bằng tiếng Việt tự nhiên, không dịch máy. Chỉ ghép khi hai trang cùng mục đích.</p>';
	}

	/**
	 * Save meta box (writes only to the edited post; English posts are
	 * written to only if an editor deliberately changes their language).
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public static function save_meta_box( $post_id, $post ) {
		if ( ! isset( $_POST['rbhvi_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbhvi_meta_nonce'] ) ), 'rbhvi_meta_' . $post_id ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) || ! in_array( $post->post_type, RBHVI_Language::POST_TYPES, true ) ) {
			return;
		}
		$want_vi = isset( $_POST['rbhvi_lang'] ) && 'vi' === sanitize_key( wp_unslash( $_POST['rbhvi_lang'] ) );
		$is_vi   = RBHVI_Language::is_vi( $post_id );
		if ( $want_vi !== $is_vi ) {
			RBHVI_Language::set_vi( $post_id, $want_vi );
		}
		if ( ! $want_vi ) {
			if ( $is_vi ) {
				delete_post_meta( $post_id, RBHVI_Language::META_PAIR );
			}
			return;
		}
		$en_id = isset( $_POST['rbhvi_pair'] ) ? absint( $_POST['rbhvi_pair'] ) : 0;
		if ( ! $en_id ) {
			delete_post_meta( $post_id, RBHVI_Language::META_PAIR );
			return;
		}
		$error = self::pair_error( $post_id, $en_id );
		if ( $error ) {
			set_transient( 'rbhvi_post_notice_' . get_current_user_id(), $error, 120 );
			return;
		}
		update_post_meta( $post_id, RBHVI_Language::META_PAIR, $en_id );
	}

	/**
	 * Show a pairing error after saving in the classic editor.
	 */
	public static function post_notice() {
		$message = get_transient( 'rbhvi_post_notice_' . get_current_user_id() );
		if ( $message ) {
			delete_transient( 'rbhvi_post_notice_' . get_current_user_id() );
			echo '<div class="notice notice-error"><p>' . esc_html( 'Chưa lưu cặp ngôn ngữ: ' . $message ) . '</p></div>';
		}
	}

	/**
	 * List column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$columns['rbhvi_lang'] = 'Ngôn ngữ';
		return $columns;
	}

	/**
	 * List column value.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public static function column( $column, $post_id ) {
		if ( 'rbhvi_lang' !== $column ) {
			return;
		}
		if ( RBHVI_Language::is_vi( $post_id ) ) {
			$pair = (int) get_post_meta( $post_id, RBHVI_Language::META_PAIR, true );
			echo esc_html( 'VI' . ( $pair ? ' ↔ EN #' . $pair : '' ) );
			return;
		}
		$vi = RBHVI_Language::vi_ids_paired_with( $post_id );
		echo esc_html( 'EN' . ( $vi ? ' ↔ VI #' . $vi[0] : '' ) );
	}

	/**
	 * Category add field.
	 */
	public static function term_field_add() {
		wp_nonce_field( 'rbhvi_term', 'rbhvi_term_nonce' );
		echo '<div class="form-field"><label><input type="checkbox" name="rbhvi_term_vi" value="1"> Chuyên mục tiếng Việt</label><p>Hiển thị tại /vi/chuyen-muc/ten-chuyen-muc/ và chỉ chứa bài tiếng Việt.</p></div>';
	}

	/**
	 * Category edit field.
	 *
	 * @param WP_Term $term Term.
	 */
	public static function term_field_edit( $term ) {
		echo '<tr class="form-field"><th scope="row">Ngôn ngữ</th><td>';
		wp_nonce_field( 'rbhvi_term', 'rbhvi_term_nonce' );
		echo '<label><input type="checkbox" name="rbhvi_term_vi" value="1" ' . checked( RBHVI_Language::term_is_vi( $term ), true, false ) . '> Chuyên mục tiếng Việt</label><p class="description">Hiển thị tại /vi/chuyen-muc/' . esc_html( $term->slug ) . '/ và chỉ chứa bài tiếng Việt. Không đánh dấu chuyên mục tiếng Anh đang dùng.</p></td></tr>';
	}

	/**
	 * Save the category language flag.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 */
	public static function save_term( $term_id, $tt_id = 0, $taxonomy = 'category' ) {
		if ( 'category' !== $taxonomy || ! isset( $_POST['rbhvi_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rbhvi_term_nonce'] ) ), 'rbhvi_term' ) || ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		$want = ! empty( $_POST['rbhvi_term_vi'] );
		if ( RBHVI_Language::term_is_vi( $term_id ) === $want ) {
			return;
		}
		if ( $want ) {
			update_term_meta( $term_id, RBHVI_Language::TERM_META, 'vi' );
		} else {
			delete_term_meta( $term_id, RBHVI_Language::TERM_META );
		}
		RBHVI_State::clear_seo_caches();
	}
}
