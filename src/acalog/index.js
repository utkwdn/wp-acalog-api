import './editor.scss';
import { registerBlockType } from '@wordpress/blocks';

import all_programs from './programs.js';

registerBlockType( 'acalog-api/programs', {
    title: "Academic Programs Selector",
    category: "text",
    icon: "star",
    description: "A text entry field that allows the user to quickly find a program and visit its catalog entry.",

	edit: ( props ) => {



		return (
            <form>
                <label className="form-label" for="program">Search for a program:</label>
                <input className="form-control" list="programs" id="program" name="program" />
                <datalist id="programs">
                {all_programs.map((this_program) => (
                    <option value={this_program.label}></option>
                ) ) }
                </datalist>
            </form>
        );
	},

    save: ( props ) => {
		return (
            <form>
                <label className="form-label" for="program">Search for a program:</label>
                <input className="form-control" list="programs" id="program" name="program" />
                <datalist id="programs">
                {all_programs.map((this_program) => (
                    <option value={this_program.label}></option>
                ) ) }
                </datalist>
            </form>
        );
	},
} );