/**
 * Tiny helpers shared across the builder.
 */
export const uid = () => 'f_' + Math.random().toString( 36 ).slice( 2, 10 );

export const cls = ( ...args ) => args.filter( Boolean ).join( ' ' );

/**
 * Build a default field record for the given type. Mirrors PHP make_field()
 * so the client and server agree on the shape.
 */
export const makeField = ( type ) => {
	const base = {
		id: uid(),
		type,
		label: '',
		placeholder: '',
		required: false,
		help: '',
		defaultValue: '',
		width: 'full',
		cssClass: '',
	};
	switch ( type ) {
		case 'textarea':
			return { ...base, label: 'Text', rows: 4 };
		case 'number':
			return { ...base, label: 'Number', min: null, max: null, step: null };
		case 'date':
			return { ...base, label: 'Date', format: 'Y-m-d', mode: 'single' };
		case 'select':
		case 'radio':
			return {
				...base,
				label: type === 'select' ? 'Dropdown' : 'Single Choice',
				options: [
					{ label: 'Option 1', value: 'option-1' },
					{ label: 'Option 2', value: 'option-2' },
					{ label: 'Option 3', value: 'option-3' },
				],
			};
		case 'checkboxes':
			return {
				...base,
				label: 'Checkboxes',
				defaultValue: [],
				options: [
					{ label: 'Option 1', value: 'option-1' },
					{ label: 'Option 2', value: 'option-2' },
					{ label: 'Option 3', value: 'option-3' },
				],
			};
		case 'checkbox':
			return { ...base, label: 'I agree', checkboxText: 'I agree to the terms' };
		case 'email':
			return { ...base, label: 'Email', isReplyTo: false };
		case 'tel':
			return { ...base, label: 'Phone' };
		case 'url':
			return { ...base, label: 'Website' };
		case 'name':
			return { ...base, label: 'Name' };
		case 'text':
			return { ...base, label: 'Text', isSubject: false };
		case 'hidden':
			return { ...base, label: 'Hidden Field' };
		case 'heading':
			return { ...base, label: '', text: 'Section Name', level: 'h3' };
		case 'paragraph':
			return { ...base, label: '', text: 'Paragraph text describing the next section of the form.' };
		case 'divider':
			return { ...base, label: '' };
		default:
			return base;
	}
};

/** Deep-clone for state updates without external dep. */
export const clone = ( obj ) => JSON.parse( JSON.stringify( obj ) );

/** Move item in an array — returns new array. */
export const moveItem = ( arr, from, to ) => {
	const out = arr.slice();
	const [ item ] = out.splice( from, 1 );
	out.splice( to, 0, item );
	return out;
};
