<?php

require_once __DIR__ . '/../core/Debug/Debug.class.php';

/*
* This class handles the pages tracking
* It collects page's visits, visitors and ad clicks
* The pages and ads should be added to database manually
*
* @protected [function] construct_Root - its invoked in the parents __construct, and decides to start or end the visit
* @protected [function] initVisit - starts the visit, checks the Page, Visitor, and Click. Print out the Visit id for the UI
* @protected [function] endVisit - ends the visit. Checks if the visit exists, and is not ended. Updates the 'end' column to the current time
* @protected [function] Page - connect to database, check if the page exists, and returns the page if exists
* @protected [function] Visitor - check if the visitor exists, insert new one if not exist, and returns it
* @protected [function] Visit - check if previous visit exists in the session, insert the new visit, sets the new visit in the session, and returns it
* @protected [function] Click - gets the ad from the input, checks if the ad exists, insert the new click, and returns it
* @protected [function] no_arr_count - checks if variable is not array or has count 0
* @protected [function] is_num_over_0 - checks if variable is number and is bigger than 0
*/
class HandlerVisit extends Debug{

    protected function construct_Root(){

        $this->Input( 'Page/input' );
        $this->Input( 'End/input' );

        $url = $this->get( 'Page/input/get/result/post/url' );

        $visit = $this->get( 'End/input/get/result/post/visit' );

        if( gettype( "" ) === gettype( $url ) && strlen( $url ) > 0 ){

            $this->initVisit();

            $this->Database( 'closeDatabase' );

        }else if( $this->is_num_over_0( $visit ) ){

            $this->endVisit();

            $this->Database( 'closeDatabase' );

        }else $this->debug_add( 'ERROR: No POST url or ad set!' );
    }

    protected function initVisit(){

        $page = $this->Page();

        if( $page === FALSE ) return $this->debug_add( 'FAILED : HandlerVisit->initVisit() $page!' );

        $visitor = $this->Visitor();

        if( $visitor === FALSE ) return $this->debug_add( 'FAILED : HandlerVisit->initVisit() $visitor!' );

        $visit = $this->Visit( $page, $visitor );

        if( $visit === FALSE ) return $this->debug_add( 'FAILED : HandlerVisit->initVisit() $visit!' );

        echo $visit['id'];

        $click = $this->Click( $visit );

        //if( $click === FALSE ) return $this->debug_add( 'CLICK not recorded!' );//If UNCOMMENT will broke the visit ( Only in debug )
    }

    protected function endVisit(){

        $this->set(
            'End/database1/select/conditions/value',
             $this->get( 'End/input/get/result/post/visit' )
        );
        $this->Database( 'End/database1' );

        if( $this->get( 'End/database1/connect/error' ) !== FALSE ) return $this->debug_add( 'FAILED : HandlerVisit->endVisit() - SQL connection ERROR!' );

        $visit = $this->get( 'End/database1/select/result/0' );

        if( $this->no_arr_count( $visit ) || $visit['end'] !== '0' ) return $this->debug_add( 'FAILED : HandlerVisit->endVisit() - no or ended VISIT!' );

        $this->set( 'End/database2/update/conditions/value', $this->get( 'End/input/get/result/post/visit' ) );

        $this->Database( 'End/database2' );

        if( $this->get( 'End/database2/update/result' ) === FALSE ) $this->debug_add( 'FAILED : HandlerVisit->endVisit() UPDATE visit END!' );
    }

    protected function Page(){

        $this->set(
            'Page/database/select/conditions/value',
             $this->get( 'Page/input/get/result/post/url' )
        );
        $this->Database( 'Page/database' );

        if( $this->get( 'Page/database/connect/error' ) !== FALSE ){

            $this->debug_add( 'FAILED : HandlerVisit->Page() - SQL connection ERROR!' );

            return FALSE;
        }
        $page = $this->get( 'Page/database/select/result/0' );

        if( $this->no_arr_count( $page ) ){

            $this->debug_add( 'PAGE select failed!' );

            return FALSE;
        }

        return $page;
    }

    protected function Visitor(){

        $this->Database( 'Visitor/database1' );

        $user = $this->get( 'Visitor/database1/select/result/0' );

        if( $this->no_arr_count( $user ) ) return FALSE;

        $this->Database( 'Visitor/database2' );

        if( $this->get( 'Visitor/database2/insert/result' ) === TRUE ){

            $this->Database( 'Visitor/database3' );

            $user = $this->get( 'Visitor/database3/select/result/0' );

            if( $this->no_arr_count( $user ) ) return FALSE;
        }
        return $user;
    }

    protected function Visit( $page, $visitor ){

        $this->set( 'Visit/database/insert/values/1', $page['id'] );
        $this->set( 'Visit/database/insert/values/2', $visitor['id'] );

        $this->Session( 'Visit/session' );

        $previous = $this->get( 'Visit/session/get/result/visit' );

        if( $this->is_num_over_0( $previous ) === TRUE ) $this->set( 'Visit/database/insert/values/3', $previous );

        $this->Database( 'Visit/database' );

        if( $this->get( 'Visit/database/insert/error' ) !== FALSE ){

            $this->debug_add( 'VISIT insert failed!' );

            return FALSE;
        }

        $this->set( 'Visit/database1/select/conditions/conditions/0/value', $page['id'] );
        $this->set( 'Visit/database1/select/conditions/conditions/1/value', $visitor['id'] );
        $this->set( 'Visit/database1/select/conditions/conditions/2/value', $this->get( 'Visit/database/insert/values/4' ) );

        $this->Database( 'Visit/database1' );

        $visit = $this->get( 'Visit/database1/select/result/0' );

        if( $this->no_arr_count( $visit ) ){

            $this->debug_add( 'VISIT select failed!' );

            $this->get( 'manager/session' )->clear()->close();

            return FALSE;
        }

        $this->set( 'Visit/session1/set/visit', $visit['id'] );

        $this->Session( 'Visit/session1' );

        return $visit;
    }

    protected function Click( $visit ){

        $this->Input( 'Click/input' );

        $ad = $this->get( 'Click/input/get/result/post/ad' );

        if( $this->is_num_over_0( $ad ) !== TRUE ) return FALSE;

        $this->set( 'Click/database1/select/conditions/value', $ad );

        $this->Database( 'Click/database1' );

        $ad = $this->get( 'Click/database1/select/result/0' );

        if( $this->no_arr_count( $ad ) ) return FALSE;

        $this->set( 'Click/database2/insert/values/1', $ad['id'] );
        $this->set( 'Click/database2/insert/values/2', $visit['id'] );

        $this->Database( 'Click/database2' );

        return $this->get( 'Click/database2/insert/result' );
    }

    protected function no_arr_count( $arr ){

        if( gettype( array() ) !== gettype( $arr ) || count( $arr ) === 0 ) return TRUE;

        return FALSE;
    }

    protected function is_num_over_0( $num ){

        if( $num !== NULL && gettype( 0 ) === gettype( (int)$num ) && (int)$num > 0 ) return TRUE;

        return FALSE;
    }
}