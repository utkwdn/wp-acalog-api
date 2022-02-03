import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'acalog-api/programs', {
	edit: ( props ) => {
		return (
            <p>
                Hello World.
            </p>
        );
	}
} );