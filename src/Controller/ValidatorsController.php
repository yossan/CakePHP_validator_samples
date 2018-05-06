<?php
namespace App\Controller;

require_once 'Utilities.php';

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Validation\Validator;


class ValidatorsController extends AppController {

    //public $autoRender = false;

    public function initialize() {
        //$this->loadModel('');
        $this->viewBuilder()->enableAutoLayout(false);
    }

    public function beforeRender(Event $event) {
        parent::beforeRender($event);
        //$this->viewBuilder()->setLayout('');
        echo 'beforeRender', '<br>';
    }
    public function test() {
    }

    private function validation($data, $isNew=true) {
        $validator = new Validator();

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function index() {
        $this->setAction('test20');
        $this->disableAutoRender();
    }

    public function test20() {
        $this->validation20(['title'=>'kigdom','memebers'=>[
            ['name'=>'tanaka'],
            ['name'=>'yoshida']
        ]]);

        $this->validation20(['title'=>'kigdom','members'=>[
            ['name'=>'tanaka'],
            ['name'=>'']
        ]]);
    }

    private function validation20($data, $isNew=true) {
        $personValidator = new Validator();
        $personValidator
            ->add('name', 'not-blank', ['rule'=>'notBlank']);

        $validator = new Validator();
        $validator
            ->requirePresence('title')
            ->notEmpty('title')
            ->addNestedMany('members', $personValidator);


        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
        if (!empty($errors)) {
            var_dump(array_keys($errors['members'])); //エラーを起こしたindexが入っている
        }
    }

    public function test19() {
        $this->validation19(['name'=>'tanaka']);
        $this->validation19(['name'=>'tanaka', 'email'=>'tanaka@php.com']);
        $this->validation19(['name'=>'tanaka', 'email'=>'yoshida@php.com']);
    }

    private function validation19($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->add('name', 'check_name', [
                'rule' => function($value, $context) {
                    //nameとemailのlocal部が一致しているか確認
                    list($local, $host) = explode('@', $context['data']['email']);
                    if ($value === $local) {
                        return true;
                    } else {
                        return false;
                    }
                },
                'on' => function($context) {
                    //emailがない場合は、検証しない
                    if(empty($context['data']['email'])) {
                        return false;
                    } else {
                        return true;
                    }
                }
            ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test18() {
        $this->validation18(['name'=>'tanaka']);
        $this->validation18(['name'=>'']);
    }


    private function validation18($data, $isNew=true) {
        $validator = new Validator();

        $validator->add('name','check_name', [
            'rule' => 'notBlank',
            'message'=>'Input your name!',
        ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test17() {
        $this->validation17(['birth'=>'1983-08-01']);
        $this->validation17(['birth'=>'1983/08/01']);
        $this->validation17(['birth'=>'01/Aug/1983']);
    }


    private function validation17($data, $isNew=true) {
        $validator = new Validator();

        $validator->add('birth','check_birth', [
            'rule' => ['date', 'ymd']
        ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test16() {
        $this->validation16(['email'=>'tanaka@php.com']);
        //$this->validation16(['email'=>'yoshida@php.com']);
    }

    private function validation16($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->requirePresence('name',
                function($context){
                    $data = $context['data'];
                    list($local, $domain) = explode('@', $data['email']);
                    if (!empty($local)) { 
                        // nameがなくてもmailから取れた場合はOK
                        return false;
                    } else {
                        return true;
                    }
            });

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }


    public function test15() {
        $this->validation15(['name'=>'', 'email'=>'tanaka@php.com']);
        $this->validation15(['name'=>'', 'email'=>'yoshida@php.com']);
    }

    private function validation15($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->notEmpty('name',
                null, 
                function($context){
                $data = $context['data'];
                list($local, $domain) = explode('@', $data['email']);
                if ($local === 'tanaka') { 
                    // tanakaのときだけ許してやる
                    return false;
                } else {
                    return true;
                }
            });

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test14() {
        $this->validation14(['title'=>'PHP7', 'subtitle'=>'hello'], 10);
        $this->validation14(['title'=>'PHP7'], false);
        $this->validation14(['title'=>'PHP7'], 5);
    }

    public function __toString() {
        return "it's me. Validations!";
    }

    private function validation14($data, $user_id, $isNew=true) {
        $validator = new Validator();
        $validator->add('title', 'check_title', [
            'rule' => function($value, $context) use ($user_id) {
                if ($value === 'PHP7' && $user_id % 2 === 0) {
                    return true;
                } else {
                    return false;
                }
            },
        ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test13() {
        $this->validation13(['title'=>'PHP7']);
    }

    private function validation13($data, $isNew=true) {
        $validator = new Validator();
        $validator->setProvider('me', $this);
        $validator->setProvider('passed',[
            'user_id' => 10
        ]);

        $validator->add('title', 'custom', [
            'rule'=>'check_title',
            'provider'=>'me'
        ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    function check_title($value, $context) {
        echo 'value: ', $value, '<br>';
        $user_id = $context['providers']['passed']['user_id'];

        if ($user_id % 2 === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function test12() {
        $this->validation12(['title'=>'PHP7']);
    }

    private function validation12($data, $isNew=true) {
        $validator = new Validator();

        $validator->add('title', 'custom', [
            'rule'=>['minLength', 10],
        ]);
        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test11() {
        $this->validation11(['name'=>'a']);
        $this->validation11(['name'=>'sai']);
        $this->validation11(['name'=>'tanak']);
        $this->validation11(['name'=>'tanaka']);
        $this->validation11(['name'=>'田中']);
        $this->validation11(['name'=>'田中大']);
        $this->validation11(['name'=>'田中大次']);
        $this->validation11(['name'=>'田中大次郎']);
        $this->validation11(['name'=>'田中大ノ次郎']);
    }

    private function validation11($data, $isNew=true) {
        $validator = new Validator();

        $validator
            ->minLength('name', 3)
            ->maxLength('name', 5);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test10() {
        $this->validation10(['email'=>'aaa', 'confirm_email'=>'aaa']);
        $this->validation10(['email'=>'aaa']);
        $this->validation10(['confirm_email'=>'aaa']);
        $this->validation10(['email'=>'aaa', 'confirm_email'=>'bbb']);
    }

    private function validation10($data, $isNew=true) {
        $validator = new Validator();

        $validator->equalToField('email', 'confirm_email');

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test9() {
        $this->validation9([], true);
        $this->validation9(['email'=>'aaa@test.com']);
        $this->validation9(['email'=>'aaa@yahoo.co.jp']);
        $this->validation9(['email'=>'aaa@yahoo.com']);
        $this->validation9(['email'=>'aaa@test']);
        $this->validation9(['email'=>'aaa']);
        $this->validation9(['email'=>'test.com']);
        $this->validation9(['email'=>'@test.com']);
    }

    private function validation9($data, $mx=false, $isNew=true) {
        $validator = new Validator();

        $validator->email('email', $mx);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test8() {
        $this->validation8(['email'=>'test@php']);
        $this->validation8(['email'=>'']);
        $this->validation8(['email'=>null]);
        $this->validation8(['email'=>false]);
        $this->validation8(['email'=>0]);
        $this->validation8(['email'=>'0']);
    }

    private function validation8($data, $isNew=true) {
        $validator = new Validator();
        $validator->notEmpty('email');
        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test7() {
        $this->validation7(['email'=>'']);
        $this->validation7(['email'=>''], false);
        $this->validation7(['email'=>'', 'pass'=>'']);
    }

    private function validation7($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->allowEmpty([
                'email' => ['when'=>'create', 'message'=>'email can not be empty'],
                'pass'=>['message'=>'Input password!'],
            ], 'update');
        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    // allowEmpty test
    public function test6() {
        $this->validation6([]); //項目自体がないことはOK
        $this->validation6(['title'=>'', 'subtitle'=>'', 'name'=>'', 'published'=>'']); //publishedは新規作成時空はだめ
        $this->validation6(['title'=>'test', 'subtitle'=>'test2', 'name'=>'test3', 'published'=>0]);
        $this->validation6(['title'=>'test', 'subtitle'=>'test2', 'name'=>'test3', 'published'=>null]);
        $this->validation6(['title'=>'test', 'subtitle'=>'test2', 'name'=>'test3', 'published'=>'0']);
    }

    private function validation6($data,$isNew=true) {
        $validator = new Validator();
        $validator
            ->allowEmpty('title')
            ->allowEmpty('subtitle', true)
            ->allowEmpty('name', 'create')
            ->allowEmpty('published', 'update');

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test5() {
        $this->validation5([]);
        $this->validation5([
            'title'=>'test',
            'published'=>'2018-05-07'
        ]);
        $this->validation5([
            'title'=>'test',
            'published'=>'2018-05-07'
        ], false);
        $this->validation5([
            'title'=>'test',
            'published'=>'2018-05-07'
        ], false);
        $this->disableAutoRender();
    }

    private function validation5($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->requirePresence([
                'title' => ['mode'=>'create', 'message'=>'not found title'],
                'published',
                'histories' => ['mode'=>'update']
            ]);

        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test4() {
        $this->validation4([]);
        $this->validation4([], false);
        $this->validation4(['author_id' => 'test', 'published' => 'test2']);
        $this->validation4(['author_id' => 'test', 'published' => 'test2'], false);
        $this->disableAutoRender();
    }
    // requiring field presence multiple fields
    private function validation4($data, $isNew=true) {
        $validator = new Validator();
        $validator ->requirePresence([
            'author_id' => [
                'mode' => 'create',
                'message' => 'An author is require.',
            ],
            'published' => [
                'mode' => 'update',
                'message' => 'The published state is required',
            ]
        ]);
        $errors = $validator->errors($data, $isNew);
        $this->outputResult($data, $errors);
    }

    public function test3() {
        $this->validation3(['author_id' => 'test']);
        $this->validation3([]); // 新規生成時にない場合は、NG
        $this->validation3([], false); // 新規生成時出ない場合は、OK
        $this->disableAutoRender();
    }
    // requiring field presence1 multiple fields
    private function validation3($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->requirePresence(['author_id', 'author_name'], 'create');
        $errors = $validator->errors($data ,$isNew);
        $this->outputResult($data, $errors);
    }

    public function test2() {
        $this->validation2(['author_id' => 'test']);
        $this->validation2([]); // 新規生成時にない場合は、NG
        $this->validation2([], false); // 新規生成時出ない場合は、OK
        $this->disableAutoRender();
    }

    // requiring field presence1
    private function validation2($data, $isNew=true) {
        $validator = new Validator();
        $validator
            ->requirePresence('author_id', 'create');
        $errors = $validator->errors($data ,$isNew);
        $this->outputResult($data, $errors);
    }


    public function test1() {
        // validation1 に関連するテストを行う
        $this->validation1(['title' => 'hello']);
        $this->validation1(['' => 'hello']);
    }

    // basic test
    private function validation1($data) {
        $validator = new Validator();
        $validator
            ->requirePresence('title')
            ->notEmpty('title', 'Please fill this field');

        $errors = $validator->errors($data);
        $this->outputResult($data, $errors);
    }

    private function outputResult(array $data, array $errors) {
        if (empty($errors)) {
            echo '"', print_r($data, true), '"', ' is correct.<br>';
        } else {
            echo '"', print_r($data, true), '"', ' occurs errors: ', print_r($errors, true), '<br>';
        }
    }
}
