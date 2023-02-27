<template>
  <div class="seach-container">
    <div class="seach-body" @click.self="navClick">
      <div class="seach-icon-box">
        <i class="el-icon-search" />
      </div>

      <!-- 标签容器 -->
      <div class="scrollBox">
        <div v-for="item in seachList" :key="item.id" class="tagBox">
          <div class="tag">
            <span
              :class="[
                'tag-title',
                isTagDialog && tagId == item.id
                  ? 'tag-title-in'
                  : 'tag-title-active',
              ]"
              @click="tagClick(item)"
            >{{ item['label'] }}</span>
            <span v-if="isTagIcon" class="tag-close" @click="tagClose(item)">
              <i class="el-icon-close" />
            </span>
          </div>

          <!-- 标签dialog -->
          <div v-if="isTagDialog && tagId == item.id" class="tagDialog">
            <div>
              <el-radio-group v-model="item.status" @keyup.enter.native="application(item)">
                <el-radio
                  v-for="(operatorItem, index) in item.operatorList"
                  :key="index"
                  class="radioItem"
                  :label="index + 1"
                >{{ operatorItem['name'] || '' }}
                </el-radio>
              </el-radio-group>
              <el-checkbox-group
                v-model="item.checkList"
                class="checkBox"
                @keyup.enter.native="application(item)"
              >
                <el-checkbox
                  v-for="(checkboxItem, checkboxIndex) in item.default"
                  :key="checkboxIndex"
                  class="checkbox-item"
                  :label="checkboxItem.key"
                >{{ checkboxItem.name || '' }}
                </el-checkbox>
              </el-checkbox-group>
              <el-input
                v-if="!item.default.length > 0"
                v-model="item.enterValue"
                class="innerDialog-input"
                placeholder="请输入内容"
                @keyup.enter.native="application(item)"
              />
            </div>
            <div class="innerDialog-btn">
              <el-button size="small" @click="tagCancel">取消</el-button>
              <el-button
                size="small"
                type="primary"
                :disabled="!item.label"
                @click="application(item)"
              >应用
              </el-button>
            </div>
          </div>
        </div>
      </div>
      <div class="seachBox">
        <div class="seach">
          <input
            v-model="seachValue"
            :class="[
              'seach-input',
              isInnerDialog ? 'seach-input-in' : 'seach-input-active',
              isSeachDialog ? 'seach-border' : '',
            ]"
            placeholder="搜索和筛选"
            @focus="getFocus($event)"
          >
        </div>

        <!-- 搜索框dialog -->
        <div v-if="isSeachDialog" class="seachDialog">
          <ul v-if="!seachValue">
            <li v-for="item in options" :key="item.label">
              <strong class="title">{{ item.label }}</strong>
              <div v-if="item.type == 1">
                <li
                  v-for="(searchItem, seachIndex) in searchHistoryList"
                  :key="seachIndex"
                  class="history-item"
                  @click="seachItemClick(searchItem)"
                >
                  {{ searchItem['label'] }}
                </li>
              </div>
              <div v-if="item.type == 2">
                <li
                  v-for="(saveItem, saveIndex) in saveHistoryList"
                  :key="saveIndex"
                  class="history-item"
                  @click="seachItemClick(saveItem)"
                >
                  {{ saveItem['label'] }}
                </li>
              </div>
              <div v-if="item.type == 3">
                <div
                  v-for="sub_item in item.options"
                  :key="sub_item.id"
                  @click="selectItemClick($event, sub_item)"
                >
                  <div
                    :class="[
                      'filter',
                      'select',
                      sub_item.id == seachID ? 'chencked-select-item' : '',
                    ]"
                  >
                    <span class="select-item">{{ sub_item.field.name }}</span>
                  </div>
                </div>
              </div>
            </li>
          </ul>
          <ul v-else>
            <li v-if="seachValueType !== 'string'">
              <strong class="title">搜索建议</strong>
              <div
                v-for="(suggestionItem, i) in suggestionNum"
                :key="suggestionItem.id"
                @click="suggestionClick(suggestionItem)"
              >
                <div
                  :class="[
                    'filter',
                    'select',
                    keydownId == i + 1 ? 'chencked-select-item' : '',
                  ]"
                >
                  <span
                    class="select-item"
                  >{{
                     `${suggestionItem.field.name}
                         ${seachValueType == 'string' ? '包含' : '是'} `
                   }}
                    <span style="color: #8cc8ff">{{ seachValue }}</span></span>
                </div>
              </div>
            </li>
            <li v-else>
              <strong class="title">搜索建议</strong>
              <div
                v-for="(suggestionItem, i) in suggestionStr"
                :key="suggestionItem.id"
                @click="suggestionClick(suggestionItem)"
              >
                <div
                  :class="[
                    'filter',
                    'select',
                    keydownId == i + 1 ? 'chencked-select-item' : '',
                  ]"
                >
                  <span
                    class="select-item"
                  >{{
                     `${suggestionItem.field.name}
                         ${seachValueType == 'string' ? '包含' : '是'} `
                   }}
                    <span style="color: #8cc8ff">{{ seachValue }}</span></span>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div v-if="isInnerDialog" class="innerDialog">
          <div v-if="operator.length > 0" class="contain">
            <el-radio-group v-model="containRadio" @keyup.enter.native="application(item)">
              <el-radio
                v-for="(item, index) in operator"
                :key="index"
                class="radioItem"
                :label="index + 1"
              >{{ item ? item.name : '' }}
              </el-radio>
            </el-radio-group>
            <el-checkbox-group
              v-if="defaultList.length > 0"
              v-model="checkList"
              class="checkBox"
              @change="changeCheckBox"
              @keyup.enter.native="application(enterValue)"
            >
              <el-checkbox
                v-for="(checkItem, checkIndex) in defaultList"
                :key="checkIndex"
                class="checkbox-item"
                :label="checkItem.key"
              >{{ checkItem['name'] || '' }}
              </el-checkbox>
            </el-checkbox-group>
          </div>
          <el-input
            v-if="!defaultList.length > 0"
            v-model="enterValue"
            class="innerDialog-input"
            placeholder="请输入内容"
            @keyup.enter.native="application(enterValue)"
          />
          <div class="innerDialog-btn">
            <el-button
              size="small"
              @click="(isInnerDialog = false), (seachValue = '')"
            >取消
            </el-button>
            <el-button
              size="small"
              type="primary"
              :disabled="!isCheckBtn"
              @click="application(enterValue)"
            >应用
            </el-button>
          </div>
        </div>
      </div>
      <div v-if="seachList.length > 0" class="nav_operator">
        <!--        <span-->
        <!--          v-if="!isInnerDialog && !isTagDialog"-->
        <!--          @click="saveHistory"-->
        <!--        >保存</span>-->
        <span @click="seachList = []">清除</span>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    searchOptions: {
      type: Array,
      default: function() {
        return []
      }
    }
  },
  data() {
    return {
      allList: [],
      formList: [],
      seachList: [],
      checkList: [],
      defaultList: [],
      saveHistoryList: [],
      searchHistoryList: [],
      operator: [],
      field: {},
      isSeachDialog: false,
      isInnerDialog: false,
      isTagDialog: false,
      isIcon: false,
      isTagIcon: true,
      checkBtn: '',
      seachValue: '',
      enterValue: '',
      seachID: 0,
      keydownId: 1,
      containRadio: 1,
      tagId: null,
      suggestionNum: [
        {
          field: {
            key: 'campaign.id',
            name: '广告系列编号'
          },
          operator: [
            {
              name: '是',
              value: 'IN'
            },
            {
              name: '不是',
              value: 'NOT_IN'
            }
          ],
          type: 'array',
          default: [],
          id: 4
        },
        {
          field: {
            key: 'adset.id',
            name: '广告组编号'
          },
          operator: [
            {
              name: '是',
              value: 'IN'
            },
            {
              name: '不是',
              value: 'NOT_IN'
            }
          ],
          type: 'array',
          default: [],
          id: 5
        },
        {
          field: {
            key: 'ad.id',
            name: '广告编号'
          },
          operator: [
            {
              name: '是',
              value: 'IN'
            },
            {
              name: '不是',
              value: 'NOT_IN'
            }
          ],
          type: 'array',
          default: [],
          id: 6
        }
      ],
      suggestionStr: [
        {
          field: {
            key: 'campaign.name',
            name: '广告系列名称'
          },
          operator: [
            {
              name: '包含',
              value: 'CONTAIN'
            },
            {
              name: '不包含',
              value: 'NOT_CONTAIN'
            }
          ],
          type: 'string',
          default: [],
          id: 1
        },
        {
          field: {
            key: 'adset.name',
            name: '广告组名称'
          },
          operator: [
            {
              name: '包含',
              value: 'CONTAIN'
            },
            {
              name: '不包含',
              value: 'NOT_CONTAIN'
            }
          ],
          type: 'string',
          default: [],
          id: 2
        },
        {
          field: {
            key: 'ad.name',
            name: '广告名称'
          },
          operator: [
            {
              name: '包含',
              value: 'CONTAIN'
            },
            {
              name: '不包含',
              value: 'NOT_CONTAIN'
            }
          ],
          type: 'string',
          default: [],
          id: 3
        }
      ],
      mockList: [
        {
          field: {
            key: 'campaign.name',
            name: '广告系列名称'
          },
          operator: ['CONTAIN', 'NOT_CONTAIN'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.name',
            name: '广告组名称'
          },
          operator: ['CONTAIN', 'NOT_CONTAIN'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.name',
            name: '广告名称'
          },
          operator: ['CONTAIN', 'NOT_CONTAIN'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.id',
            name: '广告系列编号'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: []
        },
        {
          field: {
            key: 'adset.id',
            name: '广告组编号'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: []
        },
        {
          field: {
            key: 'ad.id',
            name: '广告编号'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: []
        },
        {
          field: {
            key: 'campaign.delivery_status',
            name: '广告系列投放状态'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'string',
          default: [
            {
              key: 'active',
              name: '投放中'
            },
            {
              key: 'deleted',
              name: '已删除'
            },
            {
              key: 'error',
              name: '错误'
            },
            {
              key: 'inactive',
              name: '已暂停'
            },
            {
              key: 'off',
              name: '已关闭'
            },
            {
              key: 'pending',
              name: '审核中'
            }
          ]
        },
        {
          field: {
            key: 'adset.delivery_status',
            name: '广告组投放状态'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: [
            {
              key: 'active',
              name: '投放中'
            },
            {
              key: 'deleted',
              name: '已删除'
            },
            {
              key: 'error',
              name: '错误'
            },
            {
              key: 'inactive',
              name: '已暂停'
            },
            {
              key: 'off',
              name: '已关闭'
            },
            {
              key: 'pending',
              name: '审核中'
            }
          ]
        },
        {
          field: {
            key: 'ad.delivery_status',
            name: '广告投放状态'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: [
            {
              key: 'active',
              name: '投放中'
            },
            {
              key: 'deleted',
              name: '已删除'
            },
            {
              key: 'error',
              name: '错误'
            },
            {
              key: 'inactive',
              name: '已暂停'
            },
            {
              key: 'off',
              name: '已关闭'
            },
            {
              key: 'pending',
              name: '审核中'
            }
          ]
        },
        {
          field: {
            key: 'campaign.objective',
            name: '营销目标'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: [
            {
              key: 'LINK_CLICKS',
              name: '流量'
            },
            {
              key: 'CONVERSIONS',
              name: '转化量'
            },
            {
              key: 'POST_ENGAGEMENT',
              name: '帖文互动'
            },
            {
              key: 'PAGE_LIKES',
              name: '主页赞'
            },
            {
              key: 'MOBILE_APP_INSTALLS',
              name: '移动应用安装量'
            },
            {
              key: 'MOBILE_APP_ENGAGEMENT',
              name: '移动应用使用率'
            },
            {
              key: 'CANVAS_APP_INSTALLS',
              name: '桌面应用安装量'
            }
          ]
        },
        {
          field: {
            key: 'adset.placement.page_types',
            name: '版位'
          },
          operator: ['ANY', 'ALL', 'NONE'],
          type: 'array',
          default: [
            {
              key: 'desktopfeed',
              name: 'Facebook 动态（桌面版）'
            },
            {
              key: 'mobilefeed',
              name: 'Facebook 动态（移动版）'
            },
            {
              key: 'rightcolumn',
              name: 'Facebook 右边栏'
            },
            {
              key: 'mobile-marketplace',
              name: 'Facebook Marketplace'
            },
            {
              key: 'instagramstream',
              name: 'Instagram 动态'
            },
            {
              key: 'instagramstory',
              name: 'Instagram 快拍'
            },
            {
              key: 'mobileexternal',
              name: 'Audience Network'
            },
            {
              key: 'messenger_home',
              name: 'Messenger 收件箱'
            },
            {
              key: 'messenger_story',
              name: 'Messenger 快拍'
            },
            {
              key: 'desktop-instream-video',
              name: 'Facebook 视频插播位（桌面版）'
            },
            {
              key: 'mobile-instream-video',
              name: 'Facebook 视频插播位（移动版）'
            }
          ]
        },
        {
          field: {
            key: 'campaign.cost_per',
            name: '单次成效费用（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.cpa',
            name: '单次操作费用（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.cpm',
            name: '千次展示费用（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.frequency',
            name: '频次（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.impressions',
            name: '展示次数（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.lifetime_spent',
            name: '总花费（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'campaign.reach',
            name: '覆盖人数（广告系列）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.cost_per',
            name: '单次成效费用（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.cpa',
            name: '单次操作费用（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.cpm',
            name: '千次展示费用（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.frequency',
            name: '频次（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.impressions',
            name: '展示次数（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.lifetime_spent',
            name: '总花费（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.reach',
            name: '覆盖人数（广告组）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.cost_per',
            name: '单次成效费用（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.cpa',
            name: '单次操作费用（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.cpm',
            name: '千次展示费用（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.frequency',
            name: '频次（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.impressions',
            name: '展示次数（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.lifetime_spent',
            name: '总花费（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'ad.reach',
            name: '覆盖人数（广告）'
          },
          operator: ['GREATER_THAN', 'LESS_THAN', 'IN_RANGE', 'NOT_IN_RANGE'],
          type: 'string',
          default: []
        },
        {
          field: {
            key: 'adset.delivery_age',
            name: '受众年龄'
          },
          operator: ['NONE', 'ANY'],
          type: 'array',
          default: [
            {
              key: '13-17',
              name: '13-17'
            },
            {
              key: '18-24',
              name: '18-24'
            },
            {
              key: '25-34',
              name: '25-34'
            },
            {
              key: '35-44',
              name: '35-44'
            },
            {
              key: '45-54',
              name: '45-54'
            },
            {
              key: '55-64',
              name: '55-64'
            },
            {
              key: '>64',
              name: '65+'
            }
          ]
        },
        {
          field: {
            key: 'adset.targeting_state',
            name: '受众如何影响广告投放'
          },
          operator: ['IN', 'NOT_IN'],
          type: 'array',
          default: [
            {
              key: 'deprecating',
              name: '将受影响'
            },
            {
              key: 'delivery_affected',
              name: '目前已受影响'
            },
            {
              key: 'delivery_paused',
              name: '未投放'
            }
          ]
        },
        {
          field: {
            key: 'adset.delivery_gender',
            name: '受众性别'
          },
          operator: ['NONE', 'ANY'],
          type: 'array',
          default: [
            {
              key: 'female',
              name: '女'
            },
            {
              key: 'male',
              name: '男'
            },
            {
              key: 'unknown',
              name: '未分类'
            }
          ]
        }
      ],
      options: [
        // {
        //   label: '近期搜索记录',
        //   type: 1
        // },
        // {
        //   label: '已保存的搜索条件',
        //   type: 2
        // },
        {
          label: '筛选条件',
          type: 3,
          options: []
        }
      ]
    }
  },
  computed: {
    isCheckBtn() {
      return this.enterValue || this.checkList.length > 0
    },
    seachValueType() {
      if (parseFloat(this.seachValue).toString() === 'NaN') {
        return 'string'
      } else {
        return 'number'
      }
    }
  },
  watch: {
    isSeachDialog: {
      handler(newVal) {
        if (newVal) {
          this.$nextTick(() => {
            let documentArray =
              document.getElementsByClassName('select-item')[this.seachID - 1]
            if (documentArray) {
              documentArray.scrollIntoView({
                inline: 'nearest',
                block: 'center'
              })
            }
            if (
              document.querySelector('.scrollBox').scrollWidth >=
              document.querySelector('.scrollBox').clientWidth
            ) {
              let tagBox =
                document.getElementsByClassName('tagBox')[
                  document.getElementsByClassName('tagBox').length - 1
                ]
              if (tagBox) {
                tagBox.scrollIntoView({
                  behavior: 'smooth',
                  inline: 'nearest',
                  block: 'end'
                })
              }
            }
          })
        }
      }
    },
    searchOptions: {
      handler(newVal) {
        const operatorList = [
          {
            name: '包含',
            value: 'CONTAIN'
          },
          {
            name: '不包含',
            value: 'NOT_CONTAIN'
          },
          {
            name: '是',
            value: 'IN'
          },
          {
            name: '不是',
            value: 'NOT_IN'
          },
          {
            name: '同时符合',
            value: 'ALL'
          },
          {
            name: '大于',
            value: 'GREATER_THAN'
          },
          {
            name: '小于',
            value: 'LESS_THAN'
          },
          {
            name: '介于',
            value: 'IN_RANGE'
          },
          {
            name: '不介于',
            value: 'NOT_IN_RANGE'
          },
          {
            name: '被包含在',
            value: 'NONE'
          },
          {
            name: '排除',
            value: 'ANY'
          }
        ]
        this.allList = newVal
        let id = 0
        this.allList.forEach((item) => {
          let list = []
          item.operator.forEach((sub_item) => {
            list.push(operatorList.find((i) => i.value === sub_item))
            item.operator = list
          })
          item['id'] = ++id
        })
        this.options.forEach((item) => {
          if (item.type === 3) {
            item.options = this.allList
          }
        })
        let historyList = JSON.parse(window.localStorage.getItem('historyList'))
        Array.isArray(historyList) ? historyList : (historyList = [])
        this.searchHistoryList = historyList
      }
    }
  },
  created() {

  },

  mounted() {
    window.addEventListener('click', (e) => {
      this.handlerBlank(e)
    })
    document.querySelector('.seach-input').addEventListener('keydown', (e) => {
      this.handlerKeydown(e)
    })
  },
  destroyed() {
    window.removeEventListener('click', this.handlerBlank, false)
    window.removeEventListener('keydown', this.handlerKeydown, false)
  },
  methods: {
    handlerKeydown(event) {
      const e = event || window.event
      if (!e) {
        return
      } else if (e && e.keyCode === 40) {
        if (this.seachValue) {
          if (this.keydownId === 3) {
            this.keydownId = 1
          } else {
            this.keydownId++
          }
        }
      } else if (e && e.keyCode === 38) {
        if (this.keydownId === 1) {
          this.keydownId = 3
        } else {
          this.keydownId--
        }
      } else if (e && e.keyCode === 13) {
        let obj = {}
        let operator = ''
        if (this.seachValueType === 'number') {
          obj = this.suggestionNum.find(
            (item, index) => index === this.keydownId
          )
          operator = '是'
        } else {
          obj = this.suggestionStr.find(
            (item, index) => index === this.keydownId
          )
          operator = '包含'
        }
        this.checkId(obj.id, () => {
          let newObj = {
            label: `${obj.field.name} ${operator} ${this.seachValue}`,
            enterValue: this.seachValue,
            status: 1,
            id: obj.id,
            operatorList: obj.operator,
            default: obj.default,
            field: obj.field,
            type: obj.type
          }
          this.seachList.push(newObj)
          this.searchHistoryList.push(newObj)
          if (this.searchHistoryList.length > 5) {
            this.searchHistoryList.splice(0, 1)
          }
          window.localStorage.setItem(
            'historyList',
            JSON.stringify(this.searchHistoryList)
          )
          this.seachValue = ''
          this.keydownId = 1
          this.requestParameter()
        })
      }
    },
    handlerBlank(e) {
      if (
        !e.target.closest('.seach-body') &&
        !e.target.closest('.innerDialog-btn')
      ) {
        this.isSeachDialog = false
      }
    },
    // 获取焦点
    getFocus() {
      if (this.isTagDialog) {
        this.isTagIcon = true
        this.isTagDialog = false
      }
      this.isSeachDialog = true
    },

    // 判断选中的这项 是否存在标签栏 如果存在就不用新增
    checkId(id, cb) {
      if (this.seachList.map((item) => item.id).includes(id)) {
        this.tagId = id
        this.isSeachDialog = false
        this.isTagDialog = true
        this.isTagIcon = false
        this.seachValue = ''
        this.$nextTick(() => {
          document.querySelector('.seach-input').blur()
        })
      } else {
        if (cb) {
          cb()
        }
      }
    },

    seachItemClick(item) {
      this.checkId(item.id, () => {
        this.seachList.push(item)
      })
      this.requestParameter()
    },

    // 点击select选项
    selectItemClick(e, item) {
      this.seachID = item.id
      this.operator = item['operator']
      this.field = item['field']
      this.defaultList = item['default']
      this.type = item['type']
      this.seachValue = item.field.name
      this.checkId(item.id, () => {
        this.isSeachDialog = false
        this.$nextTick(() => {
          this.isInnerDialog = true
        })
      })
    },

    suggestionClick(item) {
      this.seachList.push({
        label: `${item.field.name} ${item['operator'][0].name} ${this.seachValue}`,
        enterValue: this.seachValue,
        status: 1,
        id: item.id,
        operatorList: item.operator,
        default: item.default,
        checkList: this.checkList,
        field: item.field,
        type: item.type
      })
      this.seachValue = ''
      this.keydownId = 1
      this.requestParameter()
    },

    // 点击应用
    application(e) {
      let intersection = []
      const handel = (list) => {
        if (list.length > 0) {
          this.defaultList.forEach((item) => {
            list.forEach((i) => {
              if (i === item.key) {
                intersection.push(item.name)
              }
            })
          })
        }
      }
      if (typeof e === 'object') {
        e.default.forEach((val) => {
          e.checkList.forEach((i) => {
            if (i === val.key) {
              intersection.push(val.name)
            }
          })
        })
        this.isTagDialog = false
        this.isTagIcon = true
        this.isSeachDialog = true
        e.label = `${e.field.name} ${
          e.operatorList.find((item, index) => e.status === index + 1).name
        } ${e.enterValue ? e.enterValue : intersection.join(',')}`
      } else {
        handel(this.checkList)
        this.seachValue = `${this.seachValue} ${
          this.operator.find((item, index) => this.containRadio === index + 1)
            .name
        } ${this.enterValue ? this.enterValue : intersection.join(',')}`
        this.isInnerDialog = false
        let obj = {
          label: this.seachValue,
          enterValue: this.enterValue,
          status: this.containRadio,
          id: this.seachID,
          operatorList: this.operator,
          default: this.defaultList,
          checkList: this.checkList,
          field: this.field,
          type: this.type
        }
        this.seachList.push(obj)
        this.searchHistoryList.push(obj)
        if (this.searchHistoryList.length > 5) {
          this.searchHistoryList.splice(0, 1)
        }
        window.localStorage.setItem(
          'historyList',
          JSON.stringify(this.searchHistoryList)
        )
        this.seachValue = ''
        this.enterValue = ''
        this.isSeachDialog = true
      }
      this.requestParameter(() => {
        this.checkList = []
      })
    },

    // 请求参数
    requestParameter(cb) {
      this.formList = this.seachList.map((item) => {
        return {
          field: item.field.key,
          operator: item.operatorList.find(
            (i, index) => item.status === index + 1
          ).value,
          value:
            item.enterValue && item.enterValue !== ''
              ? item.type === 'array' ? [item.enterValue] : item.enterValue
              : item.type === 'array' ? item.checkList : item.checkList.join(',')
        }
      })
      this.$emit('transferForm', this.formList)
      if (cb) {
        cb()
      }
    },

    // 点击搜索栏
    navClick() {
      if (!this.isInnerDialog) {
        document.querySelector('.seach-input').focus()
      }
    },

    // checkBox 监听事件
    changeCheckBox(e) {
      this.checkList = e
    },

    // 点击标签
    tagClick(item) {
      this.tagId = item.id
      this.isTagDialog = true
      this.isTagIcon = false
      this.isSeachDialog = false
    },

    // 点击标签icon
    tagClose(e) {
      this.seachList = this.seachList.filter((item) => item.id !== e.id)
      this.requestParameter()
    },

    // 点击取消
    tagCancel() {
      this.isTagIcon = true
      this.isTagDialog = false
    },

    // 点击保存
    saveHistory() {
      this.saveHistoryList = this.seachList.map((item) => item)
      // this.formList 请求参数
    }
  }
}
</script>
<style lang="scss">
.seach-container {
  margin: 0;
  padding: 0;

  ul {
    padding: 0px;
    margin: 0;

    li {
      list-style: none;
    }
  }

  .seach-body {
    width: 100%;
    height: 30px;
    display: flex;
    position: relative;
    background-color: #fff;
    font-size: 13px;
    margin: 10px auto;
    border-radius: 5px;
    cursor: text;
  }
}

.scrollBox {
  overflow-x: auto;
  width: auto;
  display: flex;
}

.scrollBox::-webkit-scrollbar {
  width: 0;
  height: 0;
}

.tagBox {
  margin-right: 5px;
  z-index: 5;
}

.history-item {
  padding: 3px;
  font-size: 11px;
}

.history-item,
.nav_operator span,
.filter {
  cursor: pointer;
}

.history-item:last-child {
  margin-bottom: 4px;
}

.checkBox {
  display: flex;
  flex-direction: column;

  .checkbox-item {
    margin-top: 5px;
  }
}

.radioItem {
  margin-top: 5px;
}

.seach-icon-box {
  height: 100%;
  margin: 0 6px;
  display: flex;
  align-items: center;
}

.seachBox {
  margin-right: 90px;
  position: relative;
  z-index: 5;

  .seach {
    height: 100%;
    display: flex;
    align-items: center;

    .seach-input {
      width: 200px;
      height: 22px;
      font-size: 12px;
      border: 0;
      padding-left: 5px;
      border-radius: 10px;
      outline: none;
      color: rgb(83, 78, 78);
    }
  }
}

.select {
  .select-item {
    display: inline-block;
    margin: 3px 0;
  }
}

.tag {
  height: 23px;
  width: max-content;
  background-color: rgb(247, 243, 243);
  border-radius: 5px;
  margin-top: 3.5px;

  .tag-title {
    padding: 2px 20px 2px 5px;
    font-size: 12px;
    color: rgb(90, 88, 88);
  }

  .tag-close {
    padding: 0px 3px 0 3px;
  }
}

.tag-title:hover {
  background-color: rgba(221, 217, 217, 0.938);
  border-radius: 5px;
  cursor: pointer;
}

.tag-close:hover {
  background-color: rgba(235, 233, 233, 0.979);
  border-radius: 5px;
  cursor: pointer;
}

.nav_operator {
  width: 80px;
  display: flex;
  position: absolute;
  justify-content: center;
  top: 0;
  right: 0;
  color: rgb(132, 168, 235);
  line-height: 32px;

  span {
    padding-right: 8px;
    display: inline-block;
  }
}

.seachBox .seach .seach-input-in,
.tag-title-in {
  background-color: #dddddd;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  height: 30px;
}

.seachBox .seach .seach-border {
  border: 1px solid rgba(49, 156, 209, 0.75);
  box-shadow: 0px 0px 5px 0px rgba(122, 200, 239, 0.75) inset;
}

.tag-title-in {
  height: 26px;
  line-height: 26px;
  width: 180px;
}

.seach-input-active,
.tag-title-active {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.tag-title-active {
  width: auto;
}

.filter {
  font-size: 11px;
  color: rgb(73, 72, 72);
  display: flex;
  align-items: center;
}

.title {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  color: rgb(41, 40, 40);
  font-weight: 700;
}

.filter:hover,
.history-item:hover,
.chencked-select-item {
  background-color: rgb(247, 243, 243);
  border-radius: 4px;
}

.seachDialog,
.innerDialog,
.tagDialog {
  height: 200px;
  z-index: 4;
  width: 240px;
  background-color: #fff;
  overflow-y: auto;
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 10px;
}

.innerDialog,
.tagDialog {
  height: auto;
  width: 200px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.tagDialog {
  width: 180px;
  position: absolute;
}

::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  width: 6px;
  background: rgba(#101f1c, 0.1);
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius: 2em;
}

::-webkit-scrollbar-thumb {
  background-color: rgba(#484949, 0.3);
  background-clip: padding-box;
  min-height: 28px;
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius: 2em;
}

::-webkit-scrollbar-thumb:hover {
  background-color: rgba(#101f1c, 1);
}

.innerDialog-btn {
  float: right;
  margin-top: 20px;
}

.innerDialog-input {
  margin-top: 10px;
}

.childrenItem,
.tag {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
