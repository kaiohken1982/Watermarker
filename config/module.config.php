<?php
namespace Watermarker;

return array(
	'service_manager' => array(
		'factories' => array(
			'Watermarker\Watermarker\Watermarker' => new Service\WatermarkerFactory()
		),
		'aliases' => array(
			'Watermarker' => 'Watermarker\Watermarker\Watermarker'
		)
	)
);