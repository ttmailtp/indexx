
  

<?php
 /* Çözüm üretmek için yola çıkan insanlar… 
   Sizlerde dünyayı iyi bir hale getirmek istemez misiniz?
   Resim düzenleyecileri istediğiniz gibi kullanabilirsiniz. 
   Ama kurumsal.shop harici bir alanda yayınlamayınız.
   Bu tür yazılımları sürekli paylaşmaya çalışacağız.
   Bunu okuyorsan şayet, Merhaba güzel insan :)
   Pazaryeri iş modeline geçtik, sende bizimle çözüm üretmek için
   yola çıkar mısın? Ürünlerini bizimle satmak ister misini?
   Eğer istersen hemen kurumsal.shop ücretsiz üye olarak, ürünlerini ekleyerek
   satışa başlayabilirsin.
   
   Saygılarımızla.
   
   
   */
$url     = $_POST['url'];

$data    = file_get_contents_curl($url);

function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
$pattern = '/window._sharedData = (.*);/';
preg_match($pattern, $data, $matches);

if(!$matches){
 $response['status'] = 'fail';	
 echo json_encode($response);
 exit;
}
$json = $matches[1];
$data = json_decode($json, true);


if ($data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['__typename'] == "GraphImage") {
    $imagesdata         = $data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['display_resources'];
    $length             = count($imagesdata);
    $response['flag']   = 'image';
    $response['image']  = $imagesdata[$length - 1]['src'];
    $response['status'] = 'success';
	
} else {
    

    if ($data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['__typename'] == "GraphSidecar") {
        $counter      = 0;
        $multipledata = $data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['edge_sidecar_to_children']['edges'];
        foreach ($multipledata as &$media) {

            if ($media['node']['is_video'] == "true") {
                
                $response['medias'][$counter][0] = $media['node']['video_url'];
                $response['medias'][$counter][1] = 'video';
                
            } else {
                
                $length                          = count($media['node']['display_resources']);
                $response['medias'][$counter][0] = $media['node']['display_resources'][$length - 1]['src'];
                $response['medias'][$counter][1] = 'image';

            }
   
            $counter++;
            $response['flag'] = 'media';
        }
        $response['status'] = 'success';
        
    } else {
        
        if ($data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['__typename'] == "GraphVideo") {
            $videolink            = $data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['video_url'];
            $response['flag']     = 'video';
            $response['videourl'] = $videolink;
            $response['status']   = 'success';
        } else {
            
            $response['status'] = 'fail';
            
        }
        
    }   
}

$owner = $data['entry_data']['PostPage'][0]['graphql']['shortcode_media']['owner'];
$response['username']        = $owner['username'];
$response['full_name']       = $owner['full_name'];
$response['profile_pic_url'] = $owner['profile_pic_url'];


echo json_encode($response);



?>