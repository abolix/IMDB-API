
<?php
// JSON CONTENT TYPE TO SEE PRETTIER
header('Content-Type: application/json');

// INCLUDE SIMPLE HTML DOM
// MORE INFO : https://simplehtmldom.sourceforge.io/manual.htm
include('inc/simple_html_dom.php');

$IMDB_id = "tt4574334";
$IMDB = file_get_html('https://www.imdb.com/title/'.$IMDB_id.'/?ref_=ttep_ep_tt');

    // Make a Json
    $RawData = new \stdClass();



// GET TITLE
foreach($IMDB->find('div.title_wrapper h1') as $title) 
    // Add To JSON
    $RawData->title = $title->innertext;

// GET RATING
foreach($IMDB->find('div.ratingValue strong span') as $rating) 
    // Add To JSON
    $RawData->rating = $rating->innertext;

// GET RATING Count
foreach($IMDB->find('div.imdbRating a span.small') as $ratingCount) 
    // Add To JSON
    $RawData->ratingcount = $ratingCount->innertext;


    // GET TIME AVERAGE LENGTH OF MOVIE / SERIES
    $time = $IMDB->find('div.title_wrapper div.subtext time' , 0);
    // Add To JSON
    $RawData->timeAverage = $time->innertext;


    //GET CATEGORIES
    $i = 0;
    $categoriesArray = [];
    $categories = $IMDB->find('div.title_wrapper div.subtext' , 0);

    foreach ($categories->find('a') as $category) {
     $category = $category->innertext;
     array_push($categoriesArray,$category);
        $i++;
    }
    $releaseDate = end($categoriesArray); // GET RELEASE DATE
    $RawData->releaseDate = $releaseDate; // Add Release Date To JSON


    array_pop($categoriesArray); // REMOVE LAST ARRAY ( RELEASE DATE )
    $RawData->categories = $categoriesArray; // Add Categories To JSON


    // GET POSTER IMAGE
    $poster = $IMDB->find('div.slate_wrapper div.poster a img' , 0);
    // Add To JSON
    $RawData->poster = $poster->src;



    // GET DESCRIPTION
    $description = $IMDB->find('div.plot_summary  div.summary_text' , 0);
    // Add To JSON
    $RawData->description = $description->plaintext;


// GET CREATORS AND STARS
    foreach ($IMDB->find('div.plot_summary  div.credit_summary_item') as $info) {
        $title = $info->find('h4.inline' , 0)->innertext;
        if ($title == "Creators:") {
            $i = 0;
            foreach ($info->find('a') as $datalink) {
                $datalink = $datalink->innertext;
                $RawData->creators[$i] = $datalink;
                $i++;
            }
        }
          if ($title == "Stars:") {
            $i = 0;
            foreach ($info->find('a') as $datalink) {
                $datalink = $datalink->innertext;
                $RawData->stars[$i] = $datalink;
                $i++;
            }

        }
    }
    


        // CHECK IF IT'S SERIES OR MOVIE
        if (strpos($releaseDate,'Series') > 0) {
            Series();
        }



function Series() {
    $RawData = $GLOBALS['RawData'];
    $IMDB = $GLOBALS['IMDB'];
        //
        // SERIES 
        //

    
    // GET EPISODE COUNT ( ALL SEASONS )
    $episodeCount = $IMDB->find('div.bp_content  div.bp_description span.bp_sub_heading' , 0);
    // Add To JSON
    $RawData->episodeCount = $episodeCount->innertext;



    // GET Season COUNT // TODO : JUST GIVE END()
    $seasonCounts = $IMDB->find('div.seasons-and-year-nav div' , 2);
    $i = 0;
    foreach ($seasonCounts->find('a') as $seasonCount) {
        $seasonCount = $seasonCount->innertext;
        $RawData->seasonCount[$i] = $seasonCount;
        $i++;
    }


    // GET EPISODE COUNT ( ALL SEASONS )
    $seasonYears = $IMDB->find('div.seasons-and-year-nav div' , 3);
    $i = 0;
    foreach ($seasonYears->find('a') as $seasonYear) {
        $seasonYear = $seasonYear->innertext;
        $RawData->seasonYear[$i] = $seasonYear;
        $i++;
    }



    

// TODO [IF ITS SERIES]
// GET BEST EPISODE
$i = 0;
foreach($IMDB->find('div.episode-container') as $bestEpisode) {
        $RawData->bestEpisode[$i] = new \stdClass(); // DEFINE STD CLASS

        // GET BEST EPISODE NUMBER
        $bestEpisodeNumber = $bestEpisode->find('div.title-row div.mellow' , 0);

        // GET BEST EPISODE NAME
        $bestEpisodeName = $bestEpisode->find('div.title-row p a' , 0);

        // GET BEST EPISODE DESCRIPTION
        $bestEpisodeDescription = $bestEpisode->find('div.title-row' , 0);
        
        // GET BEST EPISODE RATING
        $bestEpisodeRating = $bestEpisode->find('div.ipl-rating-star span.ipl-rating-star__rating' , 0);

        // Add To JSON
        $RawData->bestEpisode[$i]->episode = $bestEpisodeNumber->innertext;
        $RawData->bestEpisode[$i]->name = $bestEpisodeName->innertext;
        $RawData->bestEpisode[$i]->description = $bestEpisodeDescription->next_sibling()->innertext;
        $RawData->bestEpisode[$i]->rate = $bestEpisodeRating->innertext;
        $i++;

}





}


// REMOVE SPACES
$RawData->title = str_replace("  ","","$RawData->title");
$RawData->timeAverage = str_replace(" ","","$RawData->timeAverage");
$RawData->description = str_replace("  ","","$RawData->description");


// ENCODE TO JSON
$EncodedData = json_encode($RawData , JSON_PRETTY_PRINT);
echo $EncodedData;
?>