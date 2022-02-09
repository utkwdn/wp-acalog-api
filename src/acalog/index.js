import './editor.scss';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'acalog-api/programs', {
    title: "Academic Programs Selector",
    category: "text",
    icon: "star",
    description: "A text entry field that allows the user to quickly find a program and visit its catalog entry.",

	edit: ( props ) => {
		return (
            <p>
                Hello World.
            </p>
        );
	},

    save: ( props ) => {
		return (
            <p>
                Hello World.
            </p>
        );
	},
} );