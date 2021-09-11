<?php
$cacheDirectory = mpdf_getcachedir();
if ( ! is_dir( $cacheDirectory . 'tmp' ) ) {
  @mkdir( $cacheDirectory . 'tmp' );
}
define( '_MPDF_PATH', dirname( __FILE__ ) . '/vendor/mpdf/' );
define( '_MPDF_TEMP_PATH', $cacheDirectory . 'tmp/' );
define( '_MPDF_TTFONTDATAPATH', _MPDF_TEMP_PATH );

function mpdf_getcachedir() {
	$directory = dirname( __FILE__ ) . '/cache/';

	if ( ! is_dir( $directory ) || ! is_writable( $directory ) ) {
    if(!chmod($directory, 0777) ) {
      chmod($directory, 0755);
    }
		if ( ! is_dir( $directory ) || ! is_writable( $directory ) ) {
			die( 'exbook can\'t access cache directory. Please verify your setup!' );
		}
	}
	return $directory;
}

function mpdf_create($post_id=0, $pdf_content_file='') {
  require_once __DIR__ . '/vendor/autoload.php';

  $post = get_post($post_id);
  $pdf_filename = $post->ID.".pdf";

  global $pdf_margin_left;
  global $pdf_margin_right;
  global $pdf_margin_top;
  global $pdf_margin_bottom;
  global $pdf_margin_header;
  global $pdf_margin_footer;
  global $pdf_html_header;
  global $pdf_html_footer;
  if ( $pdf_margin_left !== 0 && $pdf_margin_left == '' ) {
  	$pdf_margin_left = 15;
  }
  if ( $pdf_margin_right !== 0 && $pdf_margin_right == '' ) {
  	$pdf_margin_right = 15;
  }
  if ( $pdf_margin_top !== 0 && $pdf_margin_top == '' ) {
  	$pdf_margin_top = 16;
  }
  if ( $pdf_margin_bottom !== 0 && $pdf_margin_bottom == '' ) {
  	$pdf_margin_bottom = 16;
  }
  if ( $pdf_margin_header !== 0 && $pdf_margin_header == '' ) {
  	$pdf_margin_header = 9;
  }
  if ( $pdf_margin_footer !== 0 && $pdf_margin_footer == '' ) {
  	$pdf_margin_footer = 9;
  }
  if ( empty( $pdf_html_header ) ) {
  	$pdf_html_header = false;
  }
  if ( empty( $pdf_html_footer ) ) {
  	$pdf_html_footer = false;
  }
  global $pdf_orientation;
  if ( $pdf_orientation == '' ) {
  	$pdf_orientation = 'P';
  }

  $templatePath = dirname( __FILE__ ) . '/templates/';
  $assetsPath = dirname( __FILE__ ) . '/assets/';

  $cp = 'utf-8';
  //$mpdf = new \Mpdf\Mpdf( $cp, 'A4', '', '', $pdf_margin_left, $pdf_margin_right, $pdf_margin_top, $pdf_margin_bottom, $pdf_margin_header, $pdf_margin_footer, $pdf_orientation );
  $mpdf = new \Mpdf\Mpdf( [
      'mode' => $cp,
      'format' => 'A4',
      'orientation' => $pdf_orientation,
      'margin_left' => $pdf_margin_left,
      'margin_right' => $pdf_margin_right,
      'margin_top' => $pdf_margin_top,
      'margin_bottom' => $pdf_margin_bottom,
      'margin_header' => $pdf_margin_header,
      'margin_footer' => $pdf_margin_footer,

  ]);

  //$mpdf->SetUserRights();
  $mpdf->title2annots = false;
  $mpdf->use_embeddedfonts_1252 = true;
  $mpdf->SetBasePath( $templatePath );
  $mpdf->SetAuthor( 'exex' );
  $mpdf->SetCreator( 'exex' );

  require_once($pdf_content_file);

  //The Header and Footer
  global $pdf_footer;
  global $pdf_header;

  if ( file_exists( $assetsPath . 'nwswa_reservation_pdf.css' ) ) {
		//Read the StyleCSS
		$pdf_css_content = file_get_contents( $assetsPath . 'nwswa_reservation_pdf.css' );
		$mpdf->WriteHTML( $pdf_css_content, 1 );
	}

  if ( $pdf_html_header ) {
  	$mpdf->SetHTMLHeader( $pdf_header );
  } else {
  	$mpdf->setHeader( $pdf_header );
  }

  if ( $pdf_html_footer ) {
  	$mpdf->SetHTMLFooter( $pdf_footer );
  } else {
  	$mpdf->setFooter( $pdf_footer );
  }

  $mpdf->WriteHTML( $pdf_content_html );

  $mpdf->Output( $pdf_filename, 'I' );
}
