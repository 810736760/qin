<template>
  <div class="app-container">
    <el-row v-loading="loadingStatus" style="margin-bottom: 10px">
      <el-col :xs="24" :sm="24" :md="9" :lg="14" :xl="16">
        <el-radio-group v-model="base" style="margin-right: 10px" @change="getTableData">
          <el-radio-button label="app">按产品</el-radio-button>
          <el-radio-button label="user">按用户</el-radio-button>
        </el-radio-group>
        <el-radio-group v-model="compareDate" @change="getTableData">
          <el-radio-button label="1">日环比</el-radio-button>
          <el-radio-button label="7">周环比</el-radio-button>
        </el-radio-group>
      </el-col>
      <el-col :xs="24" :sm="24" :md="15" :lg="10" :xl="8">
        <el-button
          v-if="signTime"
          circle
          size="mini"
          icon="el-icon-refresh-left"
          type="primary"
          @click="getTableData"
        />
        <span style="color: orange;">  美国时间 : {{ usaTime }}</span>
        <span>统计时间 : {{ signTime }}</span>

      </el-col>
    </el-row>
    <el-row v-loading="loadingStatus">
      <el-col v-if="base==='app'">
        <el-row v-for="(item,index) in tableList" :key="index" style="margin-bottom: 30px">
          <el-table
            :data="item.list"
            border
          >
            <el-table-column
              :label="item.name"
              align="center"
              width="150"
            >
              <template slot-scope="scope">
                {{ fmtPlatformName(scope.row.platform) }}
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="花费"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[0]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="营收"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[1]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="安装"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[2]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="ROI"
            >
              <template slot-scope="scope">
                <span v-html="scope.row['roi']" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="CPI"
            >
              <template slot-scope="scope">
                <span v-html="scope.row['cpi']" />
              </template>
            </el-table-column>

            <el-table-column
              label="详情"
              align="center"
              width="50"
            >
              <template slot-scope="scope">
                <el-button
                  v-if="power === 2"
                  circle
                  size="mini"
                  icon="el-icon-location-outline"
                  type="primary"
                  @click="goToInsights"
                />
                <el-button
                  v-else
                  circle
                  size="mini"
                  icon="el-icon-user"
                  type="primary"
                  @click="showUser(scope.row,item.name)"
                />
              </template>
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
      <el-col v-if="base==='user'">
        <el-row v-for="(item,index) in tableList" :key="index" style="margin-bottom: 30px">
          <el-table
            :data="item.list"
            border
          >
            <el-table-column
              :label="item.name"
              prop="name"
              align="center"
              width="150"
            />

            <el-table-column
              align="center"
              label="花费"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[0]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="营收"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[1]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="安装"
            >
              <template slot-scope="scope">
                <span v-html="scope.row[2]" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="ROI"
            >
              <template slot-scope="scope">
                <span v-html="scope.row['roi']" />
              </template>
            </el-table-column>

            <el-table-column
              align="center"

              label="CPI"
            >
              <template slot-scope="scope">
                <span v-html="scope.row['cpi']" />
              </template>
            </el-table-column>

            <el-table-column
              label="详情"
              align="center"
              width="50"
            >
              <template slot-scope="scope">
                <el-button
                  circle
                  size="mini"
                  icon="el-icon-mobile-phone"
                  type="primary"
                  @click="showApp(scope.row.platform,scope.row.name,scope.row.code)"
                />
              </template>
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
    </el-row>
    <el-dialog
      :visible.sync="dialogVisible"
      :title="dialogTitle"
    >
      <el-row v-for="(item,index) in userData" :key="index" style="margin-bottom: 30px">
        <el-table
          :data="item"
          border
        >
          <el-table-column
            :label="index"
            align="center"
            prop="name"
          />

          <el-table-column
            align="center"

            label="花费"
          >
            <template slot-scope="scope">
              <span v-html="scope.row[0]" />
            </template>
          </el-table-column>

          <el-table-column
            align="center"

            label="营收"
          >
            <template slot-scope="scope">
              <span v-html="scope.row[1]" />
            </template>
          </el-table-column>

          <el-table-column
            align="center"

            label="安装"
          >
            <template slot-scope="scope">
              <span v-html="scope.row[2]" />
            </template>
          </el-table-column>

          <el-table-column
            align="center"

            label="ROI"
          >
            <template slot-scope="scope">
              <span v-html="scope.row['roi']" />
            </template>
          </el-table-column>

          <el-table-column
            align="center"

            label="CPI"
          >
            <template slot-scope="scope">
              <span v-html="scope.row['cpi']" />
            </template>
          </el-table-column>

          <el-table-column
            v-if="base=== 'user'"
            label="数据"
            align="center"
            width="50"
          >
            <template slot-scope="scope">
              <el-button
                circle
                size="mini"
                icon="el-icon-mobile-phone"
                type="primary"
                @click="showAdSetInsights(scope.row.platform,scope.row.code,scope.row.name)"
              />
            </template>
          </el-table-column>

        </el-table>
      </el-row>
      <el-dialog
        :visible.sync="userSetDataVisible"
        :close-on-click-modal="false"
        :title="userSetDataTitle"
        append-to-body
      >
        <el-table
          v-loading="userSetDataLoading"
          :data="userSetData"
          border
        >
          <el-table-column
            label="广告集"
            align="center"
            width="180"
            show-overflow-tooltip
          >
            <template slot-scope="scope">
              <span class="likeHref" @click="showInInsight(scope.row.account_id, scope.row.set_id ,2)">
                {{ scope.row.set_name }}({{ scope.row.set_id }}})
              </span>
            </template>
          </el-table-column>

          <el-table-column
            label="花费"
            align="center"
            prop="spend"
            sortable
          />
          <el-table-column
            label="营收"
            align="center"
            prop="revenue"
            sortable
          />
          <el-table-column
            label="ROI"
            align="center"
            prop="roi"
            sortable
          />
          <el-table-column
            label="CPI"
            align="center"
            prop="cpi"
            sortable
          />

          <el-table-column
            label="广告系列"
            align="center"
            width="120"
            show-overflow-tooltip
          >
            <template slot-scope="scope">
              <span class="likeHref" @click="showInInsight(scope.row.account_id, scope.row.campaign_id ,1)">
                {{ scope.row.campaign_name }}({{ scope.row.campaign_id }}})
              </span>
            </template>
          </el-table-column>
          <el-table-column
            label="广告账户"
            align="center"
            width="120"
            show-overflow-tooltip
          >
            <template slot-scope="scope">
              <span class="likeHref" @click="showInInsight(scope.row.account_id, scope.row.account_id ,0)">
                {{ scope.row.account_name }}({{ scope.row.account_id }}})
              </span>
            </template>
          </el-table-column>
        </el-table>
      </el-dialog>
    </el-dialog>

  </div>
</template>

<script>

import { Link } from '@/utils/link'

export default {
  props: {
    realtimeType: {
      type: String, default: 'fb'
    }
  },
  data() {
    return {
      base: 'app',
      adReview: 1,
      signTime: '',
      signDate: '',
      userSetDataVisible: false,
      userSetDataLoading: false,
      userSetDataTitle: '',
      userSetData: [],
      usaTime: '',
      tableList: [],
      platform: {},
      group: [],
      compareDate: 1,
      loadingStatus: false,
      dialogTitle: '',
      dialogVisible: false,
      userData: {},
      power: this.$store.getters.user_info.power
    }
  },
  computed: {
    platformMap() {
      return this.tool.arrayColumn(this.platform, 'name', 'platform')
    },
    groupInfo() {
      // const temp = {}
      // for (const i in this.group) {
      //   if (this.tool.isSet(temp, this.group[i]['name'])) {
      //     temp[this.group[i]['name']].push(this.group[i]['uid'])
      //   } else {
      //     temp[this.group[i]['name']] = [this.group[i]['uid']]
      //   }
      // }
      // return temp
      return this.tool.arrayColumn(this.group, 'name', 'uid')
    }
  },
  created() {
    this.getTableData()
  },
  methods: {
    getTableData() {
      this.loadingStatus = true
      const _that = this
      this.request('realtime', {
        'compare_date': this.compareDate,
        'base': this.base,
        'type': this.realtimeType
      }).then(
        response => {
          _that.loadingStatus = false
          const res = response.data
          _that.signTime = res.sign_time
          _that.signDate = res.sign_date
          _that.usaTime = res.usa_time
          _that.tableList = res.list
          _that.platform = res.system
          _that.group = res.group
        }
      )
    },
    fmtPlatformName(index) {
      if (index === -1) {
        return '合计'
      }
      return this.platformMap[index] || index
    },
    showUser(row, name) {
      if (this.tool.empty(row.user)) {
        this.$message.warning('无数据')
        return
      }

      const data = {}
      for (const i in row.user) {
        const one = row.user[i]
        const key = this.groupInfo[one['uid']] || '未分配组'
        if (this.tool.isSet(data, key)) {
          data[key].push(one)
        } else {
          data[key] = [one]
        }
      }
      this.userData = data
      this.dialogVisible = true
      this.dialogTitle = '用户详情: ' + name + '-' + this.fmtPlatformName(row.platform)
    },
    showAdSetInsights(platform, code, name) {
      this.userSetDataVisible = true
      this.userSetDataTitle = name + '在[' + this.fmtPlatformName(platform) + ']投的广告集数据'
      const _that = this
      this.userSetDataLoading = true
      this.request('userDataFromSet', {
        'plat_form': platform,
        'code': code,
        'date': this.signDate
      }).then(
        response => {
          _that.userSetDataLoading = false
          const res = response.data
          _that.userSetData = res.list
        }
      ).catch(() => {
        _that.userSetDataLoading = false
      })
    },
    showApp(row, name, code) {
      if (this.tool.empty(row)) {
        this.$message.warning('无数据')
        return
      }

      const data = {}
      for (const i in row) {
        let one = row[i]

        const key = this.fmtPlatformName(one.platform)
        one.name = name
        one.code = code
        if (this.tool.isSet(data, key)) {
          data[key].push(one)
        } else {
          data[key] = [one]
        }
      }
      this.userData = data
      this.dialogVisible = true
      this.dialogTitle = name + '在各个包的使用详情'
    },
    showInInsight(aid, cid, index) {
      Link.showInInsight(aid, cid, index)
    },
    goToInsights() {
      this.$router.push({ path: '/adCenter/insights' })
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
