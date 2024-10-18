<?php

namespace Core\Validator;

/**
 * 필터링 함수 클래스
 */
class Sanitizer
{
    /**
     * 객체 속성 중 문자열 속성을 XSS 필터링
     * @param object $object 필터링할 객체
     * @param array $exclude_attrs 필터링 제외 속성
     * @return void
     */
    public static function cleanXssAll(object &$object, array $exclude_attrs = []): void
    {
        foreach ($object as $key => $value) {
            if (!in_array($key, $exclude_attrs)) {
                if (is_string($value)) {
                    $object->$key = self::cleanXss($value);
                } else {
                    $object->$key = $value;
                }
            }
        }
    }

    /**
     * 문자열을 "\n"으로 나누고, 중복을 제거한 후 다시 합쳐서 반환하는 함수.
     * 주로 textarea에서 사용자가 입력한 금지어나 차단 도메인을 정리할 때 사용
     * @param string $text
     * @return string|null
     */
    public static function removeDuplicateLines(?string $text) {
        if (!$text) {
            return null;
        }

        $lines = explode("\n", $text);
        $trimmed_lines = array_map('trim', $lines); // 각 줄에서 공백 제거
        $unique_lines = array_unique(array_filter($trimmed_lines)); // 중복 제거 및 빈 줄 필터링

        // 오름차순 정렬
        sort($unique_lines);
        
        return implode("\n", $unique_lines);
    }

    /**
     * XSS 어트리뷰트 태그 제거
     * - common.lib.php의 clean_xss_attributes() 함수
     * @param string $str
     * @return string
     */
    private static function cleanXss(string $string)
    {
        $xss_attributes_string = 'onAbort|onActivate|onAttribute|onAfterPrint|onAfterScriptExecute|onAfterUpdate|onAnimationCancel|onAnimationEnd|onAnimationIteration|onAnimationStart|onAriaRequest|onAutoComplete|onAutoCompleteError|onAuxClick|onBeforeActivate|onBeforeCopy|onBeforeCut|onBeforeDeactivate|onBeforeEditFocus|onBeforePaste|onBeforePrint|onBeforeScriptExecute|onBeforeUnload|onBeforeUpdate|onBegin|onBlur|onBounce|onCancel|onCanPlay|onCanPlayThrough|onCellChange|onChange|onClick|onClose|onCommand|onCompassNeedsCalibration|onContextMenu|onControlSelect|onCopy|onCueChange|onCut|onDataAvailable|onDataSetChanged|onDataSetComplete|onDblClick|onDeactivate|onDeviceLight|onDeviceMotion|onDeviceOrientation|onDeviceProximity|onDrag|onDragDrop|onDragEnd|onDragEnter|onDragLeave|onDragOver|onDragStart|onDrop|onDurationChange|onEmptied|onEnd|onEnded|onError|onErrorUpdate|onExit|onFilterChange|onFinish|onFocus|onFocusIn|onFocusOut|onFormChange|onFormInput|onFullScreenChange|onFullScreenError|onGotPointerCapture|onHashChange|onHelp|onInput|onInvalid|onKeyDown|onKeyPress|onKeyUp|onLanguageChange|onLayoutComplete|onLoad|onLoadedData|onLoadedMetaData|onLoadStart|onLoseCapture|onLostPointerCapture|onMediaComplete|onMediaError|onMessage|onMouseDown|onMouseEnter|onMouseLeave|onMouseMove|onMouseOut|onMouseOver|onMouseUp|onMouseWheel|onMove|onMoveEnd|onMoveStart|onMozFullScreenChange|onMozFullScreenError|onMozPointerLockChange|onMozPointerLockError|onMsContentZoom|onMsFullScreenChange|onMsFullScreenError|onMsGestureChange|onMsGestureDoubleTap|onMsGestureEnd|onMsGestureHold|onMsGestureStart|onMsGestureTap|onMsGotPointerCapture|onMsInertiaStart|onMsLostPointerCapture|onMsManipulationStateChanged|onMsPointerCancel|onMsPointerDown|onMsPointerEnter|onMsPointerLeave|onMsPointerMove|onMsPointerOut|onMsPointerOver|onMsPointerUp|onMsSiteModeJumpListItemRemoved|onMsThumbnailClick|onOffline|onOnline|onOutOfSync|onPage|onPageHide|onPageShow|onPaste|onPause|onPlay|onPlaying|onPointerCancel|onPointerDown|onPointerEnter|onPointerLeave|onPointerLockChange|onPointerLockError|onPointerMove|onPointerOut|onPointerOver|onPointerUp|onPopState|onProgress|onPropertyChange|onqt_error|onRateChange|onReadyStateChange|onReceived|onRepeat|onReset|onResize|onResizeEnd|onResizeStart|onResume|onReverse|onRowDelete|onRowEnter|onRowExit|onRowInserted|onRowsDelete|onRowsEnter|onRowsExit|onRowsInserted|onScroll|onSearch|onSeek|onSeeked|onSeeking|onSelect|onSelectionChange|onSelectStart|onStalled|onStorage|onStorageCommit|onStart|onStop|onShow|onSyncRestored|onSubmit|onSuspend|onSynchRestored|onTimeError|onTimeUpdate|onTimer|onTrackChange|onTransitionEnd|onToggle|onTouchCancel|onTouchEnd|onTouchLeave|onTouchMove|onTouchStart|onTransitionCancel|onTransitionEnd|onUnload|onURLFlip|onUserProximity|onVolumeChange|onWaiting|onWebKitAnimationEnd|onWebKitAnimationIteration|onWebKitAnimationStart|onWebKitFullScreenChange|onWebKitFullScreenError|onWebKitTransitionEnd|onWheel';
        $str = strip_tags($string);
        
        do {
            $count = $temp_count = 0;
    
            $str = preg_replace(
                '/(.*)(?:' . $xss_attributes_string . ')(?:\s*=\s*)(?:\'(?:.*?)\'|"(?:.*?)")(.*)/ius',
                '$1-$2-$3-$4',
                $str,
                -1,
                $temp_count
            );
            $count += $temp_count;
    
            $str = preg_replace(
                '/(.*)(?:' . $xss_attributes_string . ')\s*=\s*(?:[^\s>]*)(.*)/ius',
                '$1$2',
                $str,
                -1,
                $temp_count
            );
            $count += $temp_count;
    
        } while ($count);
    
        return $str;
    }
}