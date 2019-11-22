
<?php
// Json Content Type
header('Content-Type: application/json');

// Using Simple HTML DOM
// More info : https://simplehtmldom.sourceforge.io/manual.htm
include ___DIR__ . '/inc/simple_html_dom.php';

$IMDB_id = 'tt4158110'; // Example (tt4158110) >> Mr. Robot
$IMDB = file_get_html('https://www.imdb.com/title/' . $IMDB_id . '/?ref_=ttep_ep_tt');

$RawData = new stdClass();


// ADD IMDB ID
$RawData->IMDB_id = $IMDB_id;

// GET TITLE
foreach ($IMDB->find('div.title_wrapper h1') as $title)
    // Add To JSON

    if ($year = $title->find('span#titleYear a', 0)) {
        $RawData->releaseYear = $year->innertext;
    }

if ($title->find('span', 0)) {
    $title->find('span', 0)->outertext = ""; // REMOVE YEAR (2019) AFTER NAME
}

$RawData->title = $title->innertext;

// GET RATING
foreach ($IMDB->find('div.ratingValue strong span') as $rating)
    // Add To JSON
    $RawData->rating = $rating->innertext;

// GET RATING Count
foreach ($IMDB->find('div.imdbRating a span.small') as $ratingCount)
    // Add To JSON
    $RawData->ratingcount = $ratingCount->innertext;


// GET TIME AVERAGE LENGTH OF MOVIE / SERIES
if ($time = $IMDB->find('div.title_wrapper div.subtext time', 0)) {
    // Add To JSON
    $RawData->timeAverage = $time->innertext;
}



//GET CATEGORIES
$categoriesArray = [];
$categories = $IMDB->find('div.title_wrapper div.subtext', 0);

foreach ($categories->find('a') as $category) {
    $category = $category->innertext;
    array_push($categoriesArray, $category);
}
$releaseDate = end($categoriesArray); // GET RELEASE DATE
$RawData->releaseDate = $releaseDate; // Add Release Date To JSON


array_pop($categoriesArray); // REMOVE LAST ARRAY ( RELEASE DATE )
$RawData->mainGenres = $categoriesArray; // Add Categories To JSON


// GET POSTER IMAGE
$poster = $IMDB->find('div.slate_wrapper div.poster a img', 0);
// Add To JSON
$RawData->poster = $poster->src;



// GET DESCRIPTION
$description = $IMDB->find('div.plot_summary  div.summary_text', 0);
// Add To JSON
$RawData->description = $description->plaintext;


// GET CREATORS AND STARS
foreach ($IMDB->find('div.plot_summary  div.credit_summary_item') as $info) {
    $title = $info->find('h4.inline', 0)->innertext;

    // GET CREATORS
    if ($title == "Creators:" || $title == "Creator:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->creators[] = $datalink;
        }
    }
    // GET STARS
    if ($title == "Stars:" || $title == "Star:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            if ($datalink !== "See full cast & crew") { // REMOVE THIS TEXT FROM LAST OBJECT
                $RawData->stars[] = $datalink;
            }
        }
    }
    // GET WRITER
    if ($title == "Writer:" || $title == "Writers:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->writers[] = $datalink;
        }
    }
    // GET DIRECTOR 
    if ($title == "Directors:" || $title == "Director:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->directors[] = $datalink;
        }
    }
}




// GET ALL GENRES
foreach ($IMDB->find('div.see-more.inline.canwrap') as $genres) {
    if ($title = $genres->find('h4.inline', 0)) {
        $title = $title->innertext;
    }
    if ($title == "Genres:" || $title == "Genre:") {
        foreach ($genres->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->allGenres[] = $datalink;
        }
    }
}


// GET MANY THINGS
foreach ($IMDB->find('div.article  div.txt-block') as $info) {

    if ($title = $info->find('h4.inline', 0)) {
        $title = $title->innertext;
    }


    // GET COUNTRIES
    if ($title == "Country:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->country[] = $datalink;
        }
    }


    // GET LANGUAGE
    if ($title == "Language:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            $RawData->language[] = $datalink;
        }
    }

    // GET OFFICAL SITES
    if ($title == "Official Sites:") {
        foreach ($info->find('a') as $datalink) {
            $datalink = $datalink->innertext;
            if ($datalink !== "See more") {
                $RawData->officalSites[] = $datalink;
            }
        }
    }
}


// AWARDS AND WINS
if ($awards = $IMDB->find('div#titleAwardsRanks', 0)) {
    foreach ($awards->find('span.awards-blurb') as $award) {
        $RawData->awards[] = $award->plaintext;
    }
}


// GET METASCORE
if ($metaScore = $IMDB->find('div.titleReviewBarItem div.metacriticScore span', 0)) { // CHECK IF THERE IS METASCORE IN IMDB PAGE
    $RawData->metaScore = $metaScore->innertext;
}

// CHECK IF IT'S SERIES OR MOVIE
if (strpos($releaseDate, 'Series') > 0) {
    // it's series
    $RawData->type = "Series";
    Series();
} else {
    $RawData->type = "Movie";
}


function Series()
{
    global $RawData, $IMDB;
    //
    // SERIES 
    //


    // GET EPISODE COUNT ( ALL SEASONS )
    $episodeCount = $IMDB->find('div.bp_content  div.bp_description span.bp_sub_heading', 0);
    // Add To JSON
    $RawData->episodeCount = $episodeCount->innertext;



    // GET Season COUNT
    $seasonCount = $IMDB->find('div.seasons-and-year-nav div', 2);
    $seasonCount = $seasonCount->find('a', 0);
    $seasonCount = $seasonCount->innertext;
    $RawData->seasonCount = $seasonCount;


    /* TODO : "SEE ALL" PROBLEM
    // GET EPISODE COUNT ( ALL SEASONS )
    $seasonYears = $IMDB->find('div.seasons-and-year-nav div' , 3);
    foreach ($seasonYears->find('a') as $seasonYear) {
        $seasonYear = $seasonYear->innertext;
        $RawData->seasonYear[] = $seasonYear;
    }
    */


    // GET BEST EPISODE
    foreach ($IMDB->find('div.episode-container') as $i => $bestEpisode) {
        $RawData->bestEpisode[$i] = new stdClass();

        // GET BEST EPISODE NUMBER
        $bestEpisodeNumber = $bestEpisode->find('div.title-row div.mellow', 0);

        // GET BEST EPISODE NAME
        $bestEpisodeName = $bestEpisode->find('div.title-row p a', 0);

        // GET BEST EPISODE DESCRIPTION
        $bestEpisodeDescription = $bestEpisode->find('div.title-row', 0);

        // GET BEST EPISODE RATING
        $bestEpisodeRating = $bestEpisode->find('div.ipl-rating-star span.ipl-rating-star__rating', 0);

        // Add To JSON
        $RawData->bestEpisode[$i]->episode = $bestEpisodeNumber->innertext;
        $RawData->bestEpisode[$i]->name = $bestEpisodeName->innertext;
        $RawData->bestEpisode[$i]->description = $bestEpisodeDescription->next_sibling()->innertext;
        $RawData->bestEpisode[$i]->rate = $bestEpisodeRating->innertext;
    }
} // END OF SERIES


// REMOVE SPACES
$RawData->title = str_replace("  ", "", $RawData->title);
if (isset($RawData->timeAverage)) {
    $RawData->timeAverage = str_replace(" ", "", $RawData->timeAverage);
}

$RawData->description = str_replace("  ", "", $RawData->description);

if (isset($RawData->awards)) {
    foreach ($RawData->awards as $i => $award) {
        $RawData->awards[$i] = str_replace("  ", "", $RawData->awards[$i]);
    }
}

// ENCODE TO JSON
$Result = json_encode($RawData, JSON_PRETTY_PRINT);
echo $Result;
?>
