import apiFetch from '@wordpress/api-fetch';

const pp = '100';
const all_programs = [ { value: null, label: '' } ];

apiFetch( { path: '/wp/v2/acalog_programs?_fields=title,id,acf.course_department,acf.program_type,acf.degree_type&per_page=' + pp, parse: false } )
	.then( response =>{ 
        return response.headers.get( 'X-WP-TotalPages' );
    })
	.then( pages =>{
	    //console.log(pages);

        let i = 1;

        while( i <= pages ){
            apiFetch( { path: '/wp/v2/acalog_programs?_fields=title,id,acf.course_department,acf.program_type,acf.degree_type&per_page=' + pp + '&page=' + i } )
                .then( response =>{
                    response.forEach( element => {
                        all_programs.push( { value: element.id, label: element.title.rendered } );
                        //console.log(element);
                    });
                } );

            i++;
        }

        //console.log(all_programs);

} );

export default all_programs;