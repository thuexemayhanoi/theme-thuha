<?php defined( 'ABSPATH' ) || exit; ?>
<footer class="site-footer">
	<div class="container">
		<?php do_action( 'rbh_footer_cta' ); ?>
		<div class="footer-grid">
			<div class="footer-business">
				<p class="footer-brand"><?php echo esc_html( rbh_setting( 'brand' ) ); ?></p>
				<?php if ( rbh_setting( 'address' ) ) : ?><address><?php echo esc_html( rbh_setting( 'address' ) ); ?></address><?php endif; ?>
				<?php if ( rbh_setting( 'hours' ) ) : ?><p><?php echo esc_html( rbh_setting( 'hours' ) ); ?></p><?php endif; ?>
				<div class="contact-links">
					<?php foreach ( array( 'phone' => 'Call', 'whatsapp' => 'WhatsApp', 'zalo' => 'Zalo' ) as $channel => $label ) : if ( ! rbh_contact_url( $channel ) ) { continue; } ?>
					<a href="<?php echo esc_url( rbh_contact_url( $channel ) ); ?>"><?php echo esc_html( $label ); ?> <span aria-hidden="true">↗</span></a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php $groups = rbh_footer_groups(); $labels = array( 'rent' => __( 'Rent a bike', 'rentbike-hanoi' ), 'guides' => __( 'Guides', 'rentbike-hanoi' ), 'company' => __( 'Information', 'rentbike-hanoi' ), 'legal' => __( 'Legal', 'rentbike-hanoi' ) ); ?>
			<?php foreach ( $groups as $key => $items ) : if ( ! $items ) { continue; } ?>
			<details class="footer-group" open>
				<summary><?php echo esc_html( $labels[ $key ] ); ?></summary>
				<nav aria-label="<?php echo esc_attr( $labels[ $key ] ); ?>"><ul>
					<?php foreach ( $items as $item ) : ?><li><?php rbh_link( $item ); ?></li><?php endforeach; ?>
				</ul></nav>
			</details>
			<?php endforeach; ?>
		</div>
		<div class="footer-bottom"><span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( rbh_setting( 'brand' ) ); ?></span><a href="#"><?php esc_html_e( 'Back to top', 'rentbike-hanoi' ); ?> ↑</a></div>
	</div>
</footer>
<?php get_template_part( 'template-parts/contact-dock' ); ?>
<?php wp_footer(); ?>
</body>
</html>
