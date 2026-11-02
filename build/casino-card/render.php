<?php
/**
 * Markup of the Casino Card block, on the front end and in the editor preview.
 *
 * @package CasinoApiClient
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$casino = \CasinoApiClient\Api_Client::get_casino( $attributes['casinoId'] );

if ( null === $casino ) {
	return;
}

$affiliate_link = esc_url( $attributes['affiliateLink'] );
$payment_hours  = (int) $casino['payment_delay_hours'];

$stats = array(
	'rtp'         => array(
		__( 'Average RTP', 'casino-api-client' ),
		number_format_i18n( (float) $casino['average_rtp'], 2 ) . '%',
	),
	'biggest-win' => array(
		__( 'Biggest win/month', 'casino-api-client' ),
		number_format_i18n( (int) $casino['biggest_win_month'] ) . ' €',
	),
	'delay'       => array(
		__( 'Payment delay', 'casino-api-client' ),
		sprintf(
			/* translators: %s: number of hours. */
			_n( '%s hour', '%s hours', $payment_hours, 'casino-api-client' ),
			number_format_i18n( $payment_hours )
		),
	),
	'limit'       => array(
		__( 'Monthly Withdrawals', 'casino-api-client' ),
		number_format_i18n( (int) $casino['monthly_withdrawal_limit'] ) . ' €',
	),
	'validated'   => array(
		__( 'Validated Withdrawals', 'casino-api-client' ),
		number_format_i18n( (int) $casino['validated_withdrawals_value'] ) . ' €',
	),
	'count'       => array(
		__( 'Number of Monthly Withdrawals', 'casino-api-client' ),
		number_format_i18n( (int) $casino['monthly_withdrawals_number'] ),
	),
);
?>
<section <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'class' => 'casino-api-card' ) ) ); ?>>
	<div class="casino-api-card__logo">
		<img src="<?php echo esc_url( $casino['logo_url'] ); ?>" alt="<?php echo esc_attr( $casino['name'] ); ?>" width="419" height="104" loading="lazy" />
	</div>
	<dl class="casino-api-card__stats">
		<?php foreach ( $stats as $stat_name => $stat ) : ?>
			<div class="casino-api-card__stat casino-api-card__stat--<?php echo esc_attr( $stat_name ); ?>">
				<dt><?php echo esc_html( $stat[0] ); ?></dt>
				<dd><?php echo esc_html( $stat[1] ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php if ( '' !== $affiliate_link ) : ?>
		<a class="casino-api-card__cta" href="<?php echo $affiliate_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>" rel="sponsored nofollow">
			<?php esc_html_e( 'Play Now', 'casino-api-client' ); ?>
		</a>
	<?php endif; ?>
</section>
