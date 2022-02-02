<?php 

header("Content-type: application/json; charset=utf-8");

$headers = get_headers('http://localhost:8888/wordpress/wp-json/wp/v2/acalog_programs?_fields=title,acf.course_department,acf.program_type,acf.degree_type&per_page=20', true);

$i = 1;

while( $i <= $headers['X-WP-TotalPages']){

    if($i == 1){
    $acalog_programs = json_decode(file_get_contents('http://localhost:8888/wordpress//wp-json/wp/v2/acalog_programs?_fields=title,acf.course_department,acf.program_type,acf.degree_type&per_page=20&page='.$i));
    }else{
        $acalog_programs = array_merge($acalog_programs, json_decode(file_get_contents('http://localhost:8888/wordpress//wp-json/wp/v2/acalog_programs?_fields=title,acf.course_department,acf.program_type,acf.degree_type&per_page=20&page='.$i))); 
    }
    $i++;

}

echo json_encode($acalog_programs);

?>