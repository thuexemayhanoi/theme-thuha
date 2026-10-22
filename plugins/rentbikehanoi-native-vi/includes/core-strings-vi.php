<?php
/**
 * Vietnamese fallback for the few WordPress core strings that appear on
 * Vietnamese front-end pages (comment form, comment list, post and comment
 * navigation). Used only on Vietnamese requests and only when WordPress has
 * no Vietnamese translation for the string (an installed language pack wins).
 *
 * Keys: "text" or "context\x04text" (same convention as gettext).
 *
 * @package RentbikeHanoiNativeVi
 */

defined( 'ABSPATH' ) || exit;

return array(
	'Leave a Reply'                             => 'Để lại bình luận',
	'Leave a Reply to %s'                       => 'Trả lời %s',
	'Cancel reply'                              => 'Hủy trả lời',
	'Post Comment'                              => 'Gửi bình luận',
	"noun\x04Comment"                           => 'Bình luận',
	'Name'                                      => 'Tên',
	'Website'                                   => 'Trang web',
	'Your email address will not be published.' => 'Email của bạn sẽ không được hiển thị công khai.',
	'Required fields are marked %s'             => 'Các mục bắt buộc được đánh dấu %s',
	'Save my name, email, and website in this browser for the next time I comment.' => 'Lưu tên, email và trang web của tôi trên trình duyệt này cho lần bình luận sau.',
	'You must be <a href="%s">logged in</a> to post a comment.' => 'Bạn cần <a href="%s">đăng nhập</a> để bình luận.',
	'Logged in as %1$s. <a href="%2$s">Edit your profile</a>. <a href="%3$s">Log out?</a>' => 'Bạn đang đăng nhập với tên %1$s. <a href="%2$s">Sửa hồ sơ</a>. <a href="%3$s">Đăng xuất?</a>',
	'Reply to %s'                               => 'Trả lời %s',
	'%s <span class="says">says:</span>'        => '%s <span class="says">viết:</span>',
	'%1$s at %2$s'                              => '%1$s lúc %2$s',
	'Your comment is awaiting moderation.'      => 'Bình luận của bạn đang chờ duyệt.',
	'Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.' => 'Bình luận của bạn đang chờ duyệt. Đây là bản xem trước; bình luận sẽ hiển thị sau khi được duyệt.',
	'Comments navigation'                       => 'Điều hướng bình luận',
	'Older comments'                            => 'Bình luận cũ hơn',
	'Newer comments'                            => 'Bình luận mới hơn',
	'Posts navigation'                          => 'Điều hướng danh sách bài viết',
	'Posts pagination'                          => 'Phân trang bài viết',
	'Comments pagination'                       => 'Phân trang bình luận',
	'Post navigation'                           => 'Điều hướng bài viết',
	'Posts'                                     => 'Bài viết',
);
