import apiFetch from '@wordpress/api-fetch';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Notice,
	PanelBody,
	Placeholder,
	SelectControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

function useCasinos() {
	const [ casinos, setCasinos ] = useState( null );

	useEffect( () => {
		let isCurrent = true;

		apiFetch( { path: '/casino-api-client/v1/casinos' } )
			.catch( () => [] )
			.then( ( items ) => {
				if ( isCurrent ) {
					setCasinos( items );
				}
			} );

		return () => {
			isCurrent = false;
		};
	}, [] );

	return casinos;
}

function CasinoSelect( { casinos, value, onChange } ) {
	const options = [
		{ value: '', label: __( 'Select a casino', 'casino-api-client' ) },
		...casinos.map( ( { id, name } ) => ( { value: id, label: name } ) ),
	];

	return (
		<SelectControl
			label={ __( 'Casino', 'casino-api-client' ) }
			value={ value }
			options={ options }
			onChange={ onChange }
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);
}

function Preview( { casinos, attributes, onSelect } ) {
	if ( casinos === null ) {
		return <Spinner />;
	}

	if ( casinos.length === 0 ) {
		return (
			<Notice status="warning" isDismissible={ false }>
				{ __(
					'No casinos available. Check Settings > Casino API Client.',
					'casino-api-client'
				) }
			</Notice>
		);
	}

	if ( attributes.casinoId === '' ) {
		return (
			<Placeholder
				icon={ metadata.icon }
				label={ __( 'Casino Card', 'casino-api-client' ) }
			>
				<CasinoSelect
					casinos={ casinos }
					value=""
					onChange={ onSelect }
				/>
			</Placeholder>
		);
	}

	return (
		<ServerSideRender block={ metadata.name } attributes={ attributes } />
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const casinos = useCasinos();
	const selectCasino = ( casinoId ) => setAttributes( { casinoId } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Casino', 'casino-api-client' ) }>
					<CasinoSelect
						casinos={ casinos ?? [] }
						value={ attributes.casinoId }
						onChange={ selectCasino }
					/>
					<TextControl
						label={ __( 'Affiliate link', 'casino-api-client' ) }
						help={ __(
							'The call to action is hidden without a link.',
							'casino-api-client'
						) }
						type="url"
						value={ attributes.affiliateLink }
						onChange={ ( affiliateLink ) =>
							setAttributes( { affiliateLink } )
						}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<Preview
					casinos={ casinos }
					attributes={ attributes }
					onSelect={ selectCasino }
				/>
			</div>
		</>
	);
}
