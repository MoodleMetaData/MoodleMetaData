<?php
/**
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 11-05-06
 * Time: 1:36 PM
 * To change this template use File | Settings | File Templates.
 */
$urls = array('https://vista4test.srv.ualberta.ca/webct/urw/lc88335066061.tp88335086061/startFrameSet.dowebct?forward=studentCourseView.dowebct&lcid=88335066061',
                'https://vista4test.srv.ualberta.ca/webct/urw/lc100761718021.tp100761739021/startFrameSet.dowebct?forward=studentCourseView.dowebct&lcid=100761718021',
                'https://vista4test.srv.ualberta.ca/webct/urw/lc112536441051.tp112536850051/startFrameSet.dowebct?forward=studentCourseView.dowebct&lcid=112536441051',
                'https://vista4test.srv.ualberta.ca/webct/urw/lc559240107021.tp559240379021/startFrameSet.dowebct?forward=studentCourseView.dowebct&lcid=559240107021');

$sso_link="hi";
require_once 'blackBoard.php';

$bb = new blackBoard("ggibeau");

echo "<html><body>";
$num = 0;
foreach($urls as $url) {
    $sso_link = $bb->getSSOLink($url);
    echo "<a href=".$sso_link . ">Link ". $num ."</a><br>";
    $num++;
}
echo "</body></html>";