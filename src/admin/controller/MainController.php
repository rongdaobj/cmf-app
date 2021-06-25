<?php
namespace app\admin\controller;

use app\admin\controller\AdminController;
use think\Db;
use think\db\Query;


class MainController extends AdminController
{
	public function index(){
        $admin = get_admin();
		if($admin['adminer'] || $admin['id'] == 1){
            $this->adminer();
            return $this->fetch('index');
        } elseif($admin['teacher']){
            $this->teacher();
            return $this->fetch('teacher');
        } else {
            $this->other();
            return $this->fetch('other');
        }
	}

    protected function adminer(){
        $base = [];

        //学员总数
        $base['student_num'] = model('index/main/Employee')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where('student', 1)
        ->where('user_status', 1)
        ->count();

        //课程总数
        $base['lesson_num'] = model('index/lesson/Lesson')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where('status', 1)
        ->count();

        //考试总数
        $base['exam_num'] = model('index/exam/Exam')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where('status', 1)
        ->count();

        $this->assign('base', $base);

        $from = input('from');
        $to = input('to');

        $study = [];
        //访客数
        $study['user_num'] =model('index/lesson/StudyLog')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where(function (Query $query) use ($from, $to) {
            if ($from) {
                $query->where('create_at', '>=', $from);
            }

            if ($to) {
                $query->where('create_at', '<=', $to);
            }
        })
        ->count("DISTINCT uid");
        

        //学习人数
        $study['study_num'] =model('index/lesson/StudyLog')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where(function (Query $query) use ($from, $to) {
            if ($from) {
                $query->where('create_at', '>=', $from);
            }

            if ($to) {
                $query->where('create_at', '<=', $to);
            }
        })
        ->group('uid')
        ->having('sum(time)>10')
        ->count();

        //学习时长
        $study['study_time'] = model('index/lesson/StudyLog')
        ->where(function (Query $query) {
            if (cmf_get_current_admin_id() != 1) {
                $query->where('uniacid', uniacid());
            }
        })
        ->where(function (Query $query) use ($from, $to) {
            if ($from) {
                $query->where('create_at', '>=', $from);
            }

            if ($to) {
                $query->where('create_at', '<=', $to);
            }
        })
        ->sum('time');

        $this->assign('study', $study);
        
        
    }

    protected function teacher(){
        $base = [];
        //课程总数
        $base['lesson_num'] = model('index/lesson/Lesson')
        ->where('uniacid', uniacid())
        ->where('uid', cmf_get_current_admin_id())
        ->where('status', 1)
        ->count();

        //考试总数
        $base['exam_num'] = model('index/exam/Exam')
        ->where('uniacid', uniacid())
        ->where('uid', cmf_get_current_admin_id())
        ->where('status', 1)
        ->count();

        //协同阅卷
        $base['team_need_num'] = model('index/exam/ExamTeam')
        ->where('uniacid', uniacid())
        ->where('uid', cmf_get_current_admin_id())
        ->where('status', 0)
        ->count();

        $base['team_done_num'] = model('index/exam/ExamTeam')
        ->where('uniacid', uniacid())
        ->where('uid', cmf_get_current_admin_id())
        ->where('status', 1)
        ->count();

        $this->assign('base', $base);
    }

    protected function other(){

    }
}