# NetCourt Notary

## Definitions

### Transaction

存证事务，可理解为逻辑上相关的一组证据，每个事务由一个事务 ID 相关联。一次事务由一个或多个存证阶段(phase)组成，每个阶段即一次实际存证请求。存证阶段可自定义，含义为自己理解和使用。

存证事务由一个全局唯一的 Token 作为凭证，该凭证由存证初始化接口完成资格核验后返回，供固定的存证人/公司多次使用，暂不不考虑失效条件。

### Entity

进行存证业务的实体。该平台作为存证行为的发起者，需要经过互联网法院的认证，并得到分配的`accountId`和签名私钥。在进行存证时，需要使用该`accountId`作为标识，并用私钥对数据进行签名，方能通过认证，完成存证行为。

在进行存证时，可以选择将`Entity`信息也提交，也可以不提交，并不会影响存证结果。`Entity`信息类型为`Identity`，具体可以参见**Types - Identity**中的介绍。

### Customer

存证客户，也即是存证的所有者。每次存证都必定是一个实名个人或有工商登记的公司企业主体。进行存证时，需要提交存证客户的基本信息，信息类型为`Identity`，具体可以参见**Types - Identity**中的介绍。

## Notary

目前存证服务器的域名为`https://check.netcourt.gov.cn`。API 接口有如下几个：

* `/api/blockChain/notaryToken` 获取存证事务 ID
* `/api/blockChain/notaryCert` 存证并获取证书

### Sign

请求每个 API 的时候，都要对关键数据进行签名，以便存证服务器进行验证数据的可信性。

签名过程如下：将签名需要用到的数据拼接成一个字符串，然后使用密钥通过 Sha256WithRSA 算法进行加密，将得到的结果进行 Hex encode 成十六进制字符串后即为签名数据。计算签名的明文数据以 UTF-8 方式编码。

### notaryToken

每次进行存证时，必须提供一个事务 ID，以便可以将不同步骤的存证数据串联起来。事务 ID 可以使用之前已经获取过的，也可以通过`notaryToken`接口进行获取。该接口的描述如下：

```
RestFul URL: /api/blockChain/notaryToken
HTTP Method: POST
Request Content Type: application/json
Response Content Type: application/json
```

可通过如下方式使用：

```php
use NetCourt\Business;
use NetCourt\NotaryClient;
use NetCourt\Identity\Person;

try {
    $customer = new Person(['certName' => 'Lin07ux', 'certNo' => '123456789012345678']);
    $business = new Business(Business::COPYRIGHT, Business::COPYRIGHT_TEXT);
    $notaryClient = new NotaryClient('accountId', 'privateKeyValue');

    $token = $notaryClient->createNotaryToken($customer, $business);
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

请求该接口时，需要提交如下的数据：

  name      |  type    | required |  comment
----------- | -------- | -------- | --------------------------------------
 accountId  | string   | yes      | 账号标识
 entity     | Identity | no       | 存证实体信息
 bizId      | integer  | yes      | 业务类型标识，枚举字段，见**Types - bizId & subBizId**
 subBizId   | string   | no       | 子类型标识，字符串串，见**Types - bizId & subBizId**
 customer   | Identity | yes      | 客户身份标识，见**Types - Identity**
 timestamp  | string   | yes      | 验签时间戳(毫秒值)，当前时刻，误差五分钟以内
 signedData | string   | yes      | 验签数据(accountId + bizId + timestamp)
 properties | string   | no       | 扩展字段

> 该接口的验签数据明文为`accountId`、`bizId`、`timestamp`三个字段拼接后的字符串。

该接口的响应数据如下：

  name        |  type  | required |  comment
------------- | ------ | -------- | -------------------------
 success      | string | yes      | 成功标志
 code         | string | yes      | 状态码，见**Types - Code**
 errMessage   | string | yes      | 错误信息
 responseData | string | no       | 事务 ID，全局唯⼀

### notaryCert

该接口用于存证，返回存证后的区块 hash 和存证证书。该接口的描述如下：

```
RestFul URL: /api/blockChain/notaryCert
HTTP Method: POST
Request Content Type: application/json
Response Content Type: application/multipart/form-data
```

可通过如下方式使用：

```php
use NetCourt\Business;
use NetCourt\NotaryClient;
use NetCourt\Identity\Person;

try {
    $customer = new Person(['certName' => 'Lin07ux', 'certNo' => '123456789012345678']);
    $business = new Business(Business::COPYRIGHT, Business::COPYRIGHT_TEXT);
    $notaryClient = new NotaryClient('accountId', 'privateKeyValue');

    // Get the block chain hash and cert file stream
    list($hash, $certStream) = $notaryClient->createNotaryCert('token', 'right notary', 'something to notary');
    // Save the cert file stream to pdf
    file_put_contents($hash.'.pdf', $certStream);
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

请求该接口时，需要提交如下的数据：

  name         |  type      | required |  comment
-------------- | ---------- | -------- | --------------------------------------
 meta          | NotaryMeta | yes      | 存证元数据
 notaryContent | string     | no       | 存证内容，一般为文件 SHA256 hash，或文本内容
 timestamp     | string     | yes      | 验签时间戳(毫秒值)，当前时刻，误差五分钟以内
 signedData    | string     | yes      | 验签数据(meta.accountId + meta.phase + timestamp)

> 该接口的验签数据明文为`meta.accountId`、`meta.phase`、`timestamp`三个字段拼接后的字符串。

该接口的响应数据分为两部分：

* 响应的文本数据放在`Blockchainresponse` Header 中，以 JSON 字符串的形式返回。
* 请求响应成功时，会将存证证书放在响应体中，以二进制方式返回。可以直接将该部分数据保存到文件中。

解析`Blockchainresponse` Header，可以得到如下的数据：

  name        |  type  | required |  comment
------------- | ------ | -------- | -------------------------
 success      | string | yes      | 成功标志
 code         | string | yes      | 状态码，见**Types - Code**
 errMessage   | string | no       | 错误信息
 responseData | string | no       | 存证上链后的区块 hash，通过此 hash 查询存证

## Types

### Identity

Identity 数据应为个人的实名信息或企业工商登记信息，主要包含如下的内容：

  name         |  type  | required |  comment
-------------- | ------ | -------- | -----------------------------------------------------
 userType      | string | yes      | 用户类别，可选值有：`PERSON`(个人)、`ENTERPRISE`(企业)
 certName      | string | yes      | 用户名称
 certType      | string | yes      | 证件类型，userType 为 PERSON 时，值只能为`IDENTITY_CARD`，<br>userType 为 ENTERPRISE，值可以为 `UNIFIED_SOCIAL_CREDIT_CODE`(统一社会信用代码) 和 `ENTERPRISE_REGISTERED_NUMBER`(企业工商注册号)
 certNo        | string | yes      | 证件号
 mobileNo      | string | no       | 手机号码
 legalPerson   | string | no       | 法人姓名，userType 为 ENTERPRISE 时必须提供
 legalPersonId | string | no       | 法人身份证，userType 为 ENTERPRISE 时必须提供
 agent         | string | no       | 经办人姓名，userType 为 ENTERPRISE 时必须提供
 agentId       | string | no       | 经办人身份证，userType 为 ENTERPRISE 时必须提供
 properties    | string | no       | 扩展属性字段

### NotaryMeta

NotaryMeta 表示存证元数据，是进行确权时必须提供的信息，包含token、存证人/商家、存证时间、地点、存证阶段等。

  name      |  type    | required |  comment
----------- | -------- | -------- | --------------------------------------
 accountId  | string   | yes      | 账号标识
 token      | string   | yes      | 事务 ID
 phase      | string   | yes      | 描述本条存证在存证事务中的阶段，用户可自行维护
 timestamp  | string   | yes      | 验签时间戳(毫秒值)，当前时刻，误差五分钟以内
 entity     | Identity | no       | 存证实体信息
 location   | Location | no       | 存证时设备和网络的基本信息
 properties | string   | no       | 扩展属性字段

### Location

Location 表示进行存证时，操作设备(用户端)的网络、设备信息。

  name      |  type  | required |  comment
----------- | ------ | -------- | ------------------
 ip         | string | yes      | IP地址
 wifiMac    | string | no       | Wi-Fi 物理理地址
 imei       | string | no       | 操作 IMEI
 imsi       | string | no       | 操作 IMSI
 latitude   | string | no       | 纬度
 longitude  | string | no       | 经度
 properties | string | no       | 扩展属性字段

### Code

Code 表示 API 响应返回的状态码

  Code                 | HTTP Status |  comment
---------------------- | ----------- | -----------------
 OK                    | 200         | 请求成功
 ACCEPTED              | 202         | 存证或信息被接受
 BAD_REQUEST           | 400         | 请求信息不不符合要求
 FORBIDDEN             | 403         | 无权限访问或授权失败
 NOT_FOUND             | 404         | 资源未找到
 REQUEST_TIMEOUT       | 408         | 请求超时
 CONFLICT              | 409         | 核验信息与链上信息不不符
 INTERNAL_SERVER_ERROR | 500         | 服务器内部错误

### bizId & subBizId

bizId 表示业务号，subBizId 表示子业务分类。

  main type      | bizId | sub type |  subBizId
---------------- | ----- | -------- | ---------------
 COPYRIGHT(版权)  | 1     | 视频      | VIDEO
                 |       | 音频      | AUDIO
                 |       | 图像      | IMAGE
                 |       | 文本      | TEXT
 CONTRACT(合同)   | 2     | HR 合同   | HR
                 |       | 租房合同   | HOUSERENTING
                 |       | 租赁合同   | LEASING
                 |       | 供应链合同 | SUPPLY_CHAIN
                 |       | 旅游合同   | TRAVEL
                 |       | 教育合同   | EDUCATION
                 |       | 保险合同   | INSURANCE
