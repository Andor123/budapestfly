import apiFetch from '@wordpress/api-fetch';

const cfg = window.wpzfBuilder || {};

apiFetch.use( apiFetch.createNonceMiddleware( cfg.nonce || '' ) );
apiFetch.use( apiFetch.createRootURLMiddleware( cfg.apiRoot ? cfg.apiRoot.replace( /\/wpzf\/v1$/, '/' ) : '/wp-json/' ) );

const base = '/wpzf/v1';

export const api = {
	getForm: ( id ) => apiFetch( { path: `${ base }/forms/${ id }` } ),
	listForms: () => apiFetch( { path: `${ base }/forms` } ),
	createForm: ( opts = {} ) => apiFetch( { path: `${ base }/forms`, method: 'POST', data: opts } ),
	updateForm: ( id, payload ) => apiFetch( { path: `${ base }/forms/${ id }`, method: 'PUT', data: payload } ),
	deleteForm: ( id ) => apiFetch( { path: `${ base }/forms/${ id }`, method: 'DELETE' } ),
	duplicateForm: ( id ) => apiFetch( { path: `${ base }/forms/${ id }/duplicate`, method: 'POST' } ),
	getTemplates: () => apiFetch( { path: `${ base }/templates` } ),
	getOptionLists: () => apiFetch( { path: `${ base }/option-lists` } ),
};
